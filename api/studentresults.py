import os
import mysql.connector
from bs4 import BeautifulSoup
import requests
from flask import Flask, request, jsonify
from flask_cors import CORS
from werkzeug.exceptions import HTTPException
from urllib3.util.retry import Retry
from requests.adapters import HTTPAdapter
import dotenv
from studentcode import search_by_national_number, extract_data

# Database Configuration & Environment Variables
dotenv.load_dotenv('/var/www/.env')

db_config = {
    'host': os.getenv('MYSQL_HOST'),
    'user': os.getenv('MYSQL_USER'),
    'password': os.getenv('MYSQL_PASSWORD'),
    'database': 'fci',
}

# Creation of session to the target website
def create_session():
    session = requests.Session()
    retry_strategy = Retry(
        total=5,
        backoff_factor=0.3,
        status_forcelist=[500, 502, 503, 504]
    )
    adapter = HTTPAdapter(max_retries=retry_strategy)
    session.mount("http://", adapter)
    session.mount("https://", adapter)
    return session

# Flask Configuration
app = Flask(__name__)
app.config['DEBUG'] = False
CORS(app, resources={r"/*": {"origins": ["https://fci.ichiwi.me"]}})
TIMEOUT = 15

# Target URLs
zu_login_url = "https://studentactivities.zu.edu.eg/Students/Registration/ed_login.aspx"
zu_main_page_url = "https://studentactivities.zu.edu.eg/Students/Registration/ED/OR_MAIN_PAGE.aspx"
zu_acad_sheet_url = "https://studentactivities.zu.edu.eg/Students/Registration/ED/ED_DET_ACAD_SHEET.aspx"
base_url = "https://studentactivities.zu.edu.eg"

# Requests for student code (using national number)
def extract_iframe_url(html_content):
    soup = BeautifulSoup(html_content, "html.parser")
    iframe = soup.find("iframe", id="ReportFramectl00_cntphmaster_UmisReportViewer")
    if iframe and iframe.get("src"):
        iframe_url = iframe.get("src")
        return f"{base_url}{iframe_url}" if iframe_url.startswith("/") else iframe_url
    return None

def extract_nested_frame_url(html_content):
    soup = BeautifulSoup(html_content, "html.parser")
    frameset = soup.find("frameset")
    if frameset:
        frames = frameset.find_all("frame")
        if len(frames) >= 2 and frames[1].get("src"):
            frame_url = frames[1].get("src")
            return f"{base_url}{frame_url}" if frame_url.startswith("/") else frame_url
    return None

# Extraction of Student Information (Phase/Name)
def extract_student_info(soup, student_data):
    mapping = {
        "ctl00_lblstudname": ("student_name", "الاسم:"),
        "ctl00_lblphase": ("phase_name", None),
    }

    for elem_id, (data_key, prefix) in mapping.items():
        elem = soup.find(id=elem_id)
        if elem:
            text = elem.text.strip()
            if prefix and prefix in text:
                text = text.replace(prefix, "").strip()
            student_data[data_key] = text

    return student_data

# Extraction of Course Results
def extract_course_results(soup):
    semester_elements = soup.find_all(class_="a329c")

    semester_names = []
    for sem_elem in semester_elements:
        semester_name_elem = sem_elem.find(class_="a146")
        if semester_name_elem:
            semester_names.append(semester_name_elem.text.strip())
        else:
            semester_names.append(f"Semester {len(semester_names) + 1}")

    if not semester_names:
        return {}

    results_by_semester = {semester: [] for semester in semester_names}

    all_codes = soup.find_all(class_="a310") 
    all_names = soup.find_all(class_="a306")
    all_hours = soup.find_all(class_="a294")
    all_gpas = soup.find_all(class_="a290")
    all_grades = soup.find_all(class_="a286")
    all_statuses = soup.find_all(class_="a282")

    all_rows = soup.find_all('tr')
    
    semester_indices = []
    for i, row in enumerate(all_rows):
        if row.find(class_="a329c"):
            semester_indices.append(i)
    
    if not semester_indices:
        single_semester = "Semester 1" if not semester_names else semester_names[0]
        results = {single_semester: []}
        
        for i in range(len(all_codes)):
            if i < len(all_codes):
                result_data = {
                    "subject_code": all_codes[i].text.strip(),
                    "subject_name": all_names[i].text.strip() if i < len(all_names) else "",
                    "credit_hours": all_hours[i].text.strip() if i < len(all_hours) else "0",
                    "gpa": all_gpas[i].text.strip() if i < len(all_gpas) else "",
                    "marks": all_grades[i].text.strip() if i < len(all_grades) else "",
                    "status": all_statuses[i].text.strip() if i < len(all_statuses) else ""
                }
                results[single_semester].append(result_data)
        
        return results

    subjects_per_semester = []
    
    semester_indices.append(len(all_rows))
    
    for i in range(len(semester_indices) - 1):
        start_idx = semester_indices[i] + 1
        end_idx = semester_indices[i + 1]
        
        count = 0
        for j in range(start_idx, end_idx):
            try:
                row = all_rows[j]
                if row.find(class_="a310"):
                    count += 1
            except IndexError:
                pass
                
        subjects_per_semester.append(count)
    
    total_subjects = len(all_codes)
    total_counted = sum(subjects_per_semester)
    
    if total_counted != total_subjects:
        base_subjects = total_subjects // len(semester_names)
        remainder = total_subjects % len(semester_names)
        
        subjects_per_semester = [base_subjects] * len(semester_names)
        
        for i in range(remainder):
            subjects_per_semester[i] += 1

    start_idx = 0
    for sem_idx, subject_count in enumerate(subjects_per_semester):
        if sem_idx >= len(semester_names):
            break
            
        semester_name = semester_names[sem_idx]
        
        for i in range(start_idx, start_idx + subject_count):
            if i < len(all_codes):
                result_data = {
                    "subject_code": all_codes[i].text.strip(),
                    "subject_name": all_names[i].text.strip() if i < len(all_names) else "",
                    "credit_hours": all_hours[i].text.strip() if i < len(all_hours) else "0",
                    "gpa": all_gpas[i].text.strip() if i < len(all_gpas) else "",
                    "marks": all_grades[i].text.strip() if i < len(all_grades) else "",
                    "status": all_statuses[i].text.strip() if i < len(all_statuses) else ""
                }
                results_by_semester[semester_name].append(result_data)
                
        start_idx += subject_count
    
    results_by_semester = {k: v for k, v in results_by_semester.items() if v}
    
    return results_by_semester

# Request to get student results
def get_student_results(national_number):
    html_response = search_by_national_number(national_number)
    student_data = extract_data(html_response)

    session = create_session()

    login_response = session.get(zu_login_url, timeout=TIMEOUT)
    login_soup = BeautifulSoup(login_response.text, "html.parser")

    form_fields = ["__VIEWSTATE", "__VIEWSTATEGENERATOR", "__EVENTVALIDATION"]
    form_data = {}
    for field in form_fields:
        elem = login_soup.find(id=field)
        form_data[field] = elem["value"]

    student_code = student_data["student_code"]
    login_data = {
        "__EVENTTARGET": "",
        "__EVENTARGUMENT": "",
        "__LASTFOCUS": "",
        "__VIEWSTATE": form_data["__VIEWSTATE"],
        "__VIEWSTATEGENERATOR": form_data["__VIEWSTATEGENERATOR"],
        "__EVENTVALIDATION": form_data["__EVENTVALIDATION"],
        "ctl00$cntphmaster$txt_StudCode": student_code,
        "ctl00$cntphmaster$txt_Nationalnum": national_number,
        "ctl00$cntphmaster$btn_Login": "تسجيل دخول"
    }

    logged_in_response = session.post(zu_login_url, data=login_data, timeout=TIMEOUT, allow_redirects=True)

    main_page_soup = BeautifulSoup(logged_in_response.text, "html.parser")
    student_data = extract_student_info(main_page_soup, student_data)

    form_data = {}
    for field in form_fields:
        elem = main_page_soup.find(id=field)
        if elem:
            form_data[field] = elem["value"]

    academic_sheet_data = {
        "__EVENTTARGET": "ctl00$lbDET_ACAD_SHEET_AR",
        "__EVENTARGUMENT": "",
        "__VIEWSTATE": form_data.get("__VIEWSTATE", ""),
        "__VIEWSTATEGENERATOR": form_data.get("__VIEWSTATEGENERATOR", ""),
        "__EVENTVALIDATION": form_data.get("__EVENTVALIDATION", "")
    }

    academic_sheet_response = session.post(logged_in_response.url, 
                                         data=academic_sheet_data,  
                                         timeout=TIMEOUT)

    if not any(url_part in academic_sheet_response.url for url_part in ["DET_ACAD_SHEET_AR.aspx"]):
        return {**student_data}

    final_response = session.get(academic_sheet_response.url, timeout=TIMEOUT)

    iframe_url = extract_iframe_url(final_response.text)

    iframe_response = session.get(iframe_url, timeout=TIMEOUT)

    nested_frame_url = extract_nested_frame_url(iframe_response.text)

    nested_frame_response = session.get(nested_frame_url, timeout=TIMEOUT)
    nested_frame_soup = BeautifulSoup(nested_frame_response.text, "html.parser")

    results = extract_course_results(nested_frame_soup)

    student_data["results"] = results
    student_data["results_status"] = f"Successfully extracted {len(results)} course results" if results else "No results found"
    return student_data

# Final routing for fci.ichiwi.me/api/studentresults
@app.route("/", methods=["POST"])
def api_get_student_results():
    data = request.get_json(force=True)
    national_number = data.get("national_number", "").strip()
    if not national_number or not national_number.isdigit() or len(national_number) != 14:
        return jsonify({"error": "Invalid national number format"}), 400

    result = get_student_results(national_number)

    if "error" in result:
        return jsonify({"error": result["error"]}), 500

    return jsonify(result), 200
@app.errorhandler(Exception)
def handle_exception(e):
    code = e.code if isinstance(e, HTTPException) else 500
    return jsonify({"error": str(e)}), code, {'Content-Type': 'application/json'}
