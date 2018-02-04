API Doc:
--------
1. Login API : 
  URL : http://127.0.0.1:8000/api/login_check
  Method Type : POST
  Body Parameter:
    username : abhishek
    password : 123456

2. Customer API's:
  2.1: List Customer API:
  URL : http://127.0.0.1:8000/api/customers
  Method Type : GET
  Header Parameter: Content-Type : application/json
                    Authorization : Bearer "TOKEN GET FROM LOGIN API"
  2.2: Add Customer API:
    URL : http://127.0.0.1:8000/api/customers
    Method Type : POST
    Body : {
            "name" : "ABCD",
            "cnp"  : "XYZ"
          }

I will add rest of the API before submission.
