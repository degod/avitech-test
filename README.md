## AviTech Email Test

This is a simple email correspondence simulation saved into a PDF file. The goal of the task is to:

- Simulate a Gmail correspondence between two Gmail addresses.
- Use a provided editable document (3.5MB in size) and copy its content into the email
bodies.
- Simulate a back-and-forth email chain 25 times, using only email body content (no
attachments).
- Generate a PDF file from the entire simulated email chain.
- The resulting PDF file should be approximately 70MB in size.

## Setting up the project locally

Before you start, ensure you have the following installed:

- Docker (up-to-date will be just fine)
- PHP version 8.3 or later
- Web browser
- Shell terminal environment

## Getting Started

1. **Clone the repository:**

   ```bash
   git clone https://github.com/degod/avitech-test.git
   ```

2. **Navigate to the project directory:**

	```bash
	cd avitech-test/
	```

3. **Install Composer dependencies:**

	```bash
	docker-compose up --build -d
	```

4. **Start the application with Laravel Sail:**

	```bash
	docker exec -it avitech-app composer install && cp .env.example .env && php artisan key:generate && touch database.sqlite
	```

5. **Logging in to container shell:**

	```bash
	docker exec -it avitech-app bash
	```

6. **Exiting container shell:**

	```bash
	exit
	```

7. **Accessing the application:**

- The application should now be running on your local environment.
- Navigate to `http://localhost:8686` in your browser to access the application and click the button for generation.
- Or better still, go to `http://localhost:8686/generate-pdf` directly for result.

8. **Stopping the application:**

	```bash
	docker-compose down
	```

## Contributing

If you encounter bugs or wish to contribute, please follow these steps:

- Fork the repository and clone it locally.
- Create a new branch (`git checkout -b feature/fix-issue`).
- Make your changes and commit them (`git commit -am 'Fix issue'`).
- Push to the branch (`git push origin feature/fix-issue`).
- Create a new Pull Request against the `main` branch, tagging `@degod`.

## Contact

For inquiries or assistance, you can reach out to Godwin Uche:

- `Email:` degodtest@gmail.com
- `Phone:` +2348024245093
