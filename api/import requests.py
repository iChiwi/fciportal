import requests
from bs4 import BeautifulSoup
import mysql.connector
from time import sleep
import os
from datetime import datetime

# Database configuration
db_config = {
    'host': '79.137.32.139',
    'user': 'ichiwi',
    'password': 'Lu#u48eYNM*R',
    'database': 'fci'
}

# Website configuration
base_url = "http://www.zufawryservices.zu.edu.eg/Militery/Views/General/GetStudInfo"
headers = {
    'User-Agent': 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36 (KHTML, like Gecko) Chrome/91.0.4472.124 Safari/537.36'
}

def get_form_fields(session):
    """Get the required form fields from the initial page"""
    try:
        response = session.get(base_url, headers=headers)
        response.raise_for_status()
        soup = BeautifulSoup(response.text, 'html.parser')
        
        viewstate = soup.find('input', {'id': '__VIEWSTATE'})
        viewstategenerator = soup.find('input', {'id': '__VIEWSTATEGENERATOR'})
        eventvalidation = soup.find('input', {'id': '__EVENTVALIDATION'})
        
        if not viewstate or not viewstategenerator or not eventvalidation:
            logger.error("Failed to find all required form fields")
            # Save the HTML for debugging
            debug_file = f"{log_dir}/form_fields_error.html"
            with open(debug_file, "w", encoding="utf-8") as f:
                f.write(response.text)
            logger.error(f"HTML saved to {debug_file}")
            raise ValueError("Could not extract all form fields")
            
        return viewstate['value'], viewstategenerator['value'], eventvalidation['value']
    except Exception as e:
        logger.error(f"Error in get_form_fields: {str(e)}")
        raise

def check_military_status(session, name, viewstate, viewstategenerator, eventvalidation):
    """Submit the form and get the military code"""
    try:
        payload = {
            '__VIEWSTATE': viewstate,
            '__VIEWSTATEGENERATOR': viewstategenerator,
            '__EVENTVALIDATION': eventvalidation,
            'ddlFactulties': '9',
            'txtName': name,
            'btnSearch2': 'بحث'
        }
        
        logger.info(f"Submitting form for: {name}")
        response = session.post(base_url, data=payload, headers=headers)
        response.raise_for_status()
        
        soup = BeautifulSoup(response.text, 'html.parser')
        
        # Check multiple possible ID values
        possible_ids = ['lblCode', 'lblMilitaryCode']
        code_element = None
        
        # Debug: log all span elements with their IDs and values
        all_spans = soup.find_all('span')
        for span in all_spans:
            if span.has_attr('id') and span.text.strip():
                logger.info(f"Found span with id: {span['id']}, value: {span.text.strip()}")
        
        for id_val in possible_ids:
            code_element = soup.find('span', {'id': id_val})
            if code_element and code_element.text.strip():
                value = code_element.text.strip()
                # Skip if the value is just asterisks
                if value and not all(c == '*' for c in value):
                    logger.info(f"Found code in element with id: {id_val}, value: {value}")
                    break
                else:
                    logger.debug(f"Found element {id_val} but it contains only asterisks")
        
        # Look for tables with military info if span not found
        if not code_element:
            tables = soup.find_all('table')
            for table in tables:
                rows = table.find_all('tr')
                for row in rows:
                    cells = row.find_all('td')
                    if len(cells) >= 2:
                        # Specifically look for "الكود" (the code) in table cells
                        cell_text = cells[0].text.strip()
                        if "الكود" in cell_text:
                            code_element = cells[1]
                            logger.info(f"Found military code in table cell with text '{cell_text}'")
                            break
        
        # Save response for debugging
        debug_file = f"{log_dir}/response_{name.replace(' ', '_')[:20]}.html"
        with open(debug_file, "w", encoding="utf-8") as f:
            f.write(response.text)
        
        # Check if we found any code
        if code_element and code_element.text.strip():
            military_code = code_element.text.strip()
            logger.info(f"Military code found: {military_code}")
            return military_code
        else:
            logger.warning(f"No military code found for {name}")
            return None
            
    except Exception as e:
        logger.error(f"Error checking military status for {name}: {str(e)}")
        return None

def main():
    logger.info("Starting military status check process")
    
    # Connect to database
    try:
        conn = mysql.connector.connect(**db_config)
        cursor = conn.cursor(dictionary=True)
        logger.info("Connected to database successfully")
        
        # Instead of clearing all student codes, we'll only process those with 'Not Found'
        # Remove the clear_query execution
    except mysql.connector.Error as err:
        logger.error(f"Database connection error: {err}")
        return
    
    # Get only students where studentcode is 'Not Found'
    try:
        cursor.execute("SELECT seat_number, name FROM students WHERE studentcode = 'Not Found' ORDER BY seat_number ASC")
        students = cursor.fetchall()
        logger.info(f"Found {len(students)} students with 'Not Found' codes to process")
    except mysql.connector.Error as err:
        logger.error(f"Error fetching students: {err}")
        return
    
    # Initialize session
    session = requests.Session()
    retries = 3  # Number of retries for form submission
    
    for student in students:
        seat_number = student['seat_number']
        name = student['name']
        
        for attempt in range(retries):
            try:
                # Get form fields
                logger.info(f"Processing seat {seat_number}: {name} (Attempt {attempt+1}/{retries})")
                viewstate, viewstategenerator, eventvalidation = get_form_fields(session)
                
                # Check military status
                military_code = check_military_status(
                    session, name, viewstate, viewstategenerator, eventvalidation
                )
                
                if military_code:
                    # Update database with studentcode
                    update_query = "UPDATE students SET studentcode = %s WHERE seat_number = %s"
                    cursor.execute(update_query, (military_code, seat_number))
                    conn.commit()
                    logger.info(f"Updated database for seat {seat_number}")
                else:
                    # If no code found, set to 'Not found' or similar indicator
                    update_query = "UPDATE students SET studentcode = 'Not Found' WHERE seat_number = %s"
                    cursor.execute(update_query, (seat_number,))
                    conn.commit()
                    logger.warning(f"Set 'Not Found' for seat {seat_number}")
                
                print(f"Processed seat {seat_number}: {name} - Military Code: {military_code or 'Not Found'}")
                
                # Success, break retry loop
                break
                
            except Exception as e:
                logger.error(f"Error processing seat {seat_number}: {str(e)}")
                if attempt < retries - 1:
                    logger.info(f"Retrying in 5 seconds...")
                    conn.rollback()
                else:
                    logger.error(f"Failed to process after {retries} attempts")
        
        # Be polite with delay between requests
    
    # Close connections
    cursor.close()
    conn.close()
    logger.info("Process completed")

if __name__ == "__main__":
    main()
