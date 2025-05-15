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
url = "http://militaryeducation.zu.edu.eg/Views/General/GetStudInfo"

# Requests for student code (using national number)
def search_by_national_number(national_number):
    session = create_session()
    response = session.get(url, timeout=TIMEOUT)
    soup = BeautifulSoup(response.text, "html.parser")
    fields = ["__VIEWSTATE", "__VIEWSTATEGENERATOR", "__EVENTVALIDATION"]
    form_data = {}
    for field in fields:
        elem = soup.find(id=field)
        form_data[field] = elem["value"] if elem else ""
    
    form_data["txtNationalNumber"] = national_number
    form_data["btnSearch"] = "بحث"

    response = session.post(url, data=form_data, timeout=TIMEOUT)
    return response.text

# Extraction of Student Information (Phase/Name/Code)
def extract_data(html_content):
    if not html_content:
        return {"error": "Empty response from university server"}
        
    soup = BeautifulSoup(html_content, "html.parser")
    
    student_name = soup.find(id="lblStudName")
    code_elem = soup.find(id="lblCode")
    phase_elem = soup.find(id="lblPhaseName")
    faculty_elem = soup.find(id="lblFacName")
    node_elem = soup.find(id="lblStudNode")
    gender_elem = soup.find(id="lblGENDER_DESCR_AR")
    nationality_elem = soup.find(id="lblNATIONALITY_DESCR_AR")

    student_code = code_elem.text.strip() if code_elem and code_elem.text else "N/A"
    
    return {
        "student_name": student_name.text.strip() if student_name and student_name.text else "N/A",
        "student_code": student_code,
        "faculty_name": faculty_elem.text.strip() if faculty_elem and faculty_elem.text else "N/A",
        "phase_name": phase_elem.text.strip() if phase_elem and phase_elem.text else "N/A",
        "node_name": node_elem.text.strip() if node_elem and node_elem.text else "N/A",
        "gender": gender_elem.text.strip() if gender_elem and gender_elem.text else "N/A",
        "nationality": nationality_elem.text.strip() if nationality_elem and nationality_elem.text else "N/A"
    }

# Store national ID in database for student code
def update_national_id(student_code, national_number):
    try:
        connection = mysql.connector.connect(**db_config)
        cursor = connection.cursor(dictionary=True)
        
        # Check if student exists in database
        check_query = """
        SELECT id FROM students 
        WHERE studentcode = %s
        """
        cursor.execute(check_query, (student_code,))
        result = cursor.fetchone()
        
        if result:
            update_query = """
            UPDATE students 
            SET nationalssn = %s 
            WHERE studentcode = %s
            """
            cursor.execute(update_query, (national_number, student_code))
            connection.commit()
            return {"success": True}
        else:
            return {"message": "Student not found in database"}
    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        return {"error": "Database error occurred"}
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()

# Final routing for fci.ichiwi.me/api/studentcode
@app.route("/", methods=["POST"])
def api_search_national():
    data = request.get_json(force=True)
    national_number = data.get("national_number", "").strip()
    if not national_number or not national_number.isdigit() or len(national_number) != 14:
        return jsonify({"error": "Invalid national number format"}), 400
    
    html_response = search_by_national_number(national_number)
    result = extract_data(html_response)

    if "error" in result:
        return jsonify({"error": result["error"]}), 500
        
    if all(value == "N/A" for value in result.values()):
        return jsonify({"error": "No student found with this national number"}), 404
    
    if result["student_code"] != "N/A":
        update_national_id(result["student_code"], national_number)
    
    return jsonify(result), 200

@app.errorhandler(Exception)
def handle_exception(e):
    code = e.code if isinstance(e, HTTPException) else 500
    return jsonify({"error": str(e)}), code, {'Content-Type': 'application/json'}
