*** Settings ***
Documentation     Test to verify Failed Login Attempt appears in System Logs action column
Library           SeleniumLibrary
Library           String
Library           DateTime

*** Variables ***
${URL}                    https://cs040268.cpkkuhost.com
${LOGIN_URL}              ${URL}/login
${DASHBOARD_URL}          ${URL}/dashboard
${BROWSER}                Chrome
${ATTEMPT_TIMESTAMP}      ${EMPTY}    # Variable to store the timestamp of the failed login attempt

# Login Page Locators
${USERNAME_INPUT}         xpath=//input[@placeholder='USERNAME' or contains(@name, 'username')]
${PASSWORD_INPUT}         xpath=//input[@placeholder='PASSWORD' or contains(@name, 'password') or @type='password']
${SUBMIT_BTN}             xpath=//button[contains(text(), 'LOG IN') or @type='submit']

# System Logs Page Locators 
${LOG_TABLE}              xpath=//table
${TOP_TWO_ROWS}           xpath=//table//tr[position() <= 3]  # First row is header, so we get rows 1-3
${LATEST_ROW}             xpath=//table//tr[2]  # The very latest log entry (first row after header)
${SECOND_LATEST_ROW}      xpath=//table//tr[3]  # The second latest log entry

# Test Data
${CORRECT_EMAIL}          admin@gmail.com
${CORRECT_PASSWORD}       123456789
${INCORRECT_EMAIL}        incorrect@example.com
${INCORRECT_PASSWORD}     wrongpassword123

*** Test Cases ***
Verify Failed Login Attempt In System Logs
    [Documentation]    Verify that Failed Login Attempt appears in the Action column of System Logs
    [Timeout]          5 minutes
    
    # Step 1: Open browser and attempt failed login
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Set Selenium Implicit Wait    5s
    Attempt Failed Login
    
    # Step 2: Login as admin (with correct credentials)
    Navigate To Login Page
    Login As Admin
    
    # Step 3: Navigate to dashboard and check for Failed Login Attempt
    Navigate To Dashboard
    Verify Failed Login In Logs
    
    # Clean up
    [Teardown]    Close Browser

*** Keywords ***
Attempt Failed Login
    [Documentation]    Attempt a login with incorrect credentials
    # Record the timestamp of the failed login attempt
    ${current_timestamp}=    Get Current Date    result_format=%Y-%m-%d %H:%M
    Set Test Variable    ${ATTEMPT_TIMESTAMP}    ${current_timestamp}
    Log    Failed login attempt timestamp: ${ATTEMPT_TIMESTAMP}
    
    # Navigate to login page
    Execute JavaScript    window.location.href='${LOGIN_URL}';
    Sleep    3s
    
    # Wait for login form elements
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=15s
    Wait Until Element Is Visible    ${PASSWORD_INPUT}    timeout=15s
    
    # Enter incorrect credentials
    Input Text    ${USERNAME_INPUT}    ${INCORRECT_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${INCORRECT_PASSWORD}
    Capture Page Screenshot    before_failed_login.png
    
    # Submit the form
    ${click_success}=    Run Keyword And Return Status    Click Element    ${SUBMIT_BTN}
    Run Keyword If    not ${click_success}    Execute JavaScript    document.querySelector('form').submit();
    
    Sleep    3s
    Capture Page Screenshot    after_failed_login.png
    Log    Attempted login with incorrect credentials: ${INCORRECT_EMAIL} / ${INCORRECT_PASSWORD}

Navigate To Login Page
    [Documentation]    Navigate directly to the login page using JavaScript
    Execute JavaScript    window.location.href='${LOGIN_URL}';
    Sleep    3s
    Capture Page Screenshot    login_page.png
    
    # Check if login elements are visible and reload if needed
    ${username_visible}=    Run Keyword And Return Status    Element Should Be Visible    ${USERNAME_INPUT}
    Run Keyword If    not ${username_visible}    Reload Page
    Run Keyword If    not ${username_visible}    Sleep    2s

Login As Admin
    [Documentation]    Login as admin user using JavaScript where possible
    # Enter correct credentials
    Input Text    ${USERNAME_INPUT}    ${CORRECT_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${CORRECT_PASSWORD}
    Capture Page Screenshot    before_login.png
    
    # Try to click submit button, fallback to JavaScript if it fails
    ${click_success}=    Run Keyword And Return Status    Click Element    ${SUBMIT_BTN}
    Run Keyword If    not ${click_success}    Execute JavaScript    document.querySelector('form').submit();
    
    Sleep    5s
    Capture Page Screenshot    after_login.png
    Log    Logged in as admin with correct credentials

Navigate To Dashboard
    [Documentation]    Navigate to the dashboard page using JavaScript
    Execute JavaScript    window.location.href='${DASHBOARD_URL}';
    Sleep    5s
    
    # Take screenshot of the dashboard
    Capture Page Screenshot    dashboard_page.png
    
    # Check for system logs table content
    ${page_has_system_logs}=    Run Keyword And Return Status    Page Should Contain    System Logs
    Run Keyword If    ${page_has_system_logs}    Log    Successfully navigated to dashboard with System Logs
    Run Keyword If    not ${page_has_system_logs}    Log    WARNING: System Logs not found on dashboard
    
    # Try scrolling to see more content if needed
    Execute JavaScript    window.scrollTo(0, 500);
    Sleep    2s
    Capture Page Screenshot    scrolled_down.png

Verify Failed Login In Logs
    [Documentation]    Check if Failed Login Attempt appears in the Action column of the most recent logs
    # Take screenshot before checking
    Capture Page Screenshot    before_verification.png
    
    # Try scrolling to where the logs table should be
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight * 0.6);
    Sleep    2s
    Capture Page Screenshot    scrolled_to_logs.png
    
    # Verify the table exists
    Wait Until Element Is Visible    ${LOG_TABLE}    timeout=10s
    
    # Check the latest row for Failed Login Attempt
    ${latest_row_text}=    Get Text    ${LATEST_ROW}
    ${second_latest_row_text}=    Get Text    ${SECOND_LATEST_ROW}
    
    # Log the content of the latest rows for debugging
    Log    Latest row content: ${latest_row_text}
    Log    Second latest row content: ${second_latest_row_text}
    
    # Check if either of the top two rows contains Failed Login Attempt
    ${latest_has_failed_login}=    Run Keyword And Return Status    Should Contain    ${latest_row_text}    Failed Login Attempt
    ${second_latest_has_failed_login}=    Run Keyword And Return Status    Should Contain    ${second_latest_row_text}    Failed Login Attempt
    
    # Check if either of the top rows contains our incorrect email
    ${latest_has_email}=    Run Keyword And Return Status    Should Contain    ${latest_row_text}    ${INCORRECT_EMAIL}
    ${second_latest_has_email}=    Run Keyword And Return Status    Should Contain    ${second_latest_row_text}    ${INCORRECT_EMAIL}
    
    # Log what we found
    Run Keyword If    ${latest_has_failed_login}    Log    SUCCESS: Latest log entry contains Failed Login Attempt
    Run Keyword If    ${second_latest_has_failed_login}    Log    SUCCESS: Second latest log entry contains Failed Login Attempt
    Run Keyword If    ${latest_has_email} or ${second_latest_has_email}    Log    SUCCESS: Found our incorrect email in the logs
    
    # Check the action c