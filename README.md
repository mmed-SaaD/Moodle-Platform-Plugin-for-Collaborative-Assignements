📌 Robot Framework Automation Project
Welcome to my test automation project using Robot Framework. This repository contains two main modules:

API Testing – Automated API tests with RequestsLibrary
Robot Framework Practice – UI exercises and workflows (Selenium)

This project aims to improve my test automation skills, build a clean framework structure, and follow best practices with Robot Framework.

🚀 Features
✔️ API Testing

GET, POST, PUT, DELETE requests
API session management
Dynamic endpoint parameterization
Response validation (status code, body, headers)

✔️ UI Testing (Selenium)

Web interface tests with SeleniumLibrary
Page Object Model (POM) pattern
Centralized locators in locator.py
Login, navigation, and UI validation demonstrations


📂 Project Structure
Robot Framework Automation Project/
│
├── API Testing/
│   ├── api_tests.robot
│   ├── variables.robot
│   └── resources/
│
├── Robot Framework Practice/
│   ├── login_tests.robot
│   ├── locator.py
│   └── resources/
│
└── README.md

🛠️ Technologies & Libraries

Robot Framework
SeleniumLibrary
RequestsLibrary
Python 3.x
Browser: Edge / Chrome
Page Object Model (POM)


▶️ Running Tests
🔹 Run all tests
bashrobot .
🔹 Run only API tests
bashrobot "API Testing"
🔹 Run only UI tests
bashrobot "Robot Framework Practice"

📘 Code Examples
GET Request (API Testing)
robotframeworkCreate Session    first_session   ${base_url}
${response}=      GET    first_session   /posts/${var}
Status Should Be  200   ${response}
POM Locator (UI Testing)
robotframework${login_btn}=    //*[@id='submit']
Click Element    ${login_btn}

🎯 Project Goals

Develop a robust automation framework
Practice Robot Framework with API + UI
Build a professional portfolio for Quality Assurance / Test Automation roles


📩 Contact
If you'd like to collaborate, provide feedback, or suggest improvements:
📧 Email: your-email@example.com
🔗 LinkedIn: Your LinkedIn Profile
