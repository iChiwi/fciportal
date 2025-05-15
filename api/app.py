from werkzeug.middleware.dispatcher import DispatcherMiddleware
from werkzeug.serving import run_simple
from studentcode import app as code_app
from studentresults import app as results_app
from studentseat import app as seatnum_app

# Importing modules
application = DispatcherMiddleware(None, {
    '/api/studentcode': code_app,
    '/api/studentresults': results_app,
    '/api/studentseat': seatnum_app,
})

# Setting up the application
app = application
if __name__ == "__main__":
    run_simple('0.0.0.0', 5000, application, use_reloader=False)
