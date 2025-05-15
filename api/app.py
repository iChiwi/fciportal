from werkzeug.middleware.dispatcher import DispatcherMiddleware
from werkzeug.serving import run_simple
from studentcode import app as code_app
from studentresults import app as results_app
from seatnumber import app as seatnumber_app

# Importing modules
application = DispatcherMiddleware(None, {
    '/api/studentcode': code_app,
    '/api/studentresults': results_app,
    '/api/seatnumber': seatnumber_app,
})

# Setting up the application
app = application
if __name__ == "__main__":
    run_simple('0.0.0.0', 5000, application, use_reloader=False)
