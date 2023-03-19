## How to run the server using php artisan serve
This project is built with Laravel framework. To run the server using php artisan serve, follow the steps below:

- Clone the repository to your local machine.
- Open a terminal and navigate to the project's directory.
- Run composer install to install all the required dependencies.
- Create a new .env file by running cp .env.example .env.
- Start the server by running php artisan serve.

## How to access the API
Once the server is running, you can access the API by sending a GET request to http://127.0.0.1:8000/api/calculate-commission with the following parameters in the query string:

## Run the feature test

php artisan test --testsuite=Feature