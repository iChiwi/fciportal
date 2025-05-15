import os
import mysql.connector
from flask import Flask, request, jsonify
from flask_cors import CORS
from werkzeug.exceptions import HTTPException
import dotenv

# Database Configuration & Environment Variables
dotenv.load_dotenv('/var/www/.env')

db_config = {
    'host': os.getenv('MYSQL_HOST'),
    'user': os.getenv('MYSQL_USER'),
    'password': os.getenv('MYSQL_PASSWORD'),
    'database': 'fci',
}

# Flask Configuration
app = Flask(__name__)
app.config['DEBUG'] = False
CORS(app, resources={r"/*": {"origins": ["https://fci.ichiwi.me"]}})

# Check database for student code match to fetch seat number
def check_database(student_code):
    try:
        connection = mysql.connector.connect(**db_config)
        cursor = connection.cursor(dictionary=True)
        
        query = """
        SELECT seat_number, section 
        FROM students 
        WHERE studentcode = %s
        """
        cursor.execute(query, (student_code,))
        result = cursor.fetchone()

        return result if result else {}
    except mysql.connector.Error as err:
        print(f"Database error: {err}")
        return {"error": "Database error occurred"}
    finally:
        if cursor:
            cursor.close()
        if connection:
            connection.close()

# Endpoint for fetching seat numbers by student code
@app.route("/", methods=["POST"])
def api_seat_number():
    data = request.get_json(force=True)
    student_code = data.get("student_code", "").strip()
    
    if not student_code:
        return jsonify({"error": "Student code is required"}), 400
    
    result = check_database(student_code)
    
    if "error" in result:
        return jsonify({"error": result["error"]}), 500
        
    if not result:
        return jsonify({"error": "No seat information found for this student code"}), 404
    
    return jsonify(result), 200

@app.errorhandler(Exception)
def handle_exception(e):
    code = e.code if isinstance(e, HTTPException) else 500
    return jsonify({"error": str(e)}), code, {'Content-Type': 'application/json'}
