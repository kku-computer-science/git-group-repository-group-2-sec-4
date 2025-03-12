*** Settings ***
Documentation     Test to verify HTTP 404 errors appear in System Logs action column
Library           SeleniumLibrary
Library           String
Library           DateTime

*** Variables ***
${URL}                    https://cs040268.cpkkuhost.com
${LOGIN_URL}              ${URL}/login
${ADMIN_URL}              ${URL}/admin
${DASHBOARD_URL}          ${URL}/dashboard
${BROWSER}                Chrome
${ERROR_TIMESTAMP}        ${EMPTY}    # Variable to store the timestamp when we generate the error
${RANDOM_URL_SEGMENT}     ${EMPTY}    # Variable to store the random URL segment we generate

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
${ADMIN_EMAIL}            admin@gmail.com
${PASSWORD}               123456789

*** Test Cases ***
Verify HTTP 404 Error In System Logs
    [Documentation]    Verify that HTTP 404 appears in the Action column of System Logs
    [Timeout]          5 minutes
    
    # Step 1: Open browser and generate 404 error
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Set Selenium Implicit Wait    5s
    Generate 404 Error
    
    # Step 2: Login as admin
    Navigate To Login Page
    Login As Admin
    
    # Step 3: Navigate to dashboard and check for HTTP 404
    Navigate To Dashboard
    Verify Recent Logs For HTTP 404
    
    # Clean up
    [Teardown]    Close Browser

*** Keywords ***
Generate 404 Error
    [Documentation]    Generate a 404 error by visiting a non-existent URL
    # Get current timestamp before generating the error
    ${current_timestamp}=    Get Current Date    result_format=%Y-%m-%d %H:%M
    Set Test Variable    ${ERROR_TIMESTAMP}    ${current_timestamp}
    Log    Error generation timestamp: ${ERROR_TIMESTAMP}
    
    # Generate a random string for the URL
    ${random_string}=    Generate Random String    8    [LOWER]
    Set Test Variable    ${RANDOM_URL_SEGMENT}    ${random_string}
    
    # Construct the non-existent URL with the random string
    ${nonexistent_url}=    Set Variable    ${URL}/non-existent-page-${RANDOM_URL_SEGMENT}
    
    Log    Generating 404 error by visiting: ${nonexistent_url}
    Go To    ${nonexistent_url}
    Sleep    3s
    Capture Page Screenshot    at_404.png
    Log    Successfully generated 404 error at ${ERROR_TIMESTAMP} with URL containing "${RANDOM_URL_SEGMENT}"

Navigate To Login Page
    [Documentation]    Navigate directly to the login page using JavaScript
    # Use JavaScript to navigate directly to login URL to avoid click issues
    Execute JavaScript    window.location.href='${LOGIN_URL}';
    Sleep    3s
    Capture Page Screenshot    login_page.png
    
    # Check if login elements are visible and reload if needed
    ${username_visible}=    Run Keyword And Return Status    Element Should Be Visible    ${USERNAME_INPUT}
    Run Keyword If    not ${username_visible}    Reload Page
    Run Keyword If    not ${username_visible}    Sleep    2s

Login As Admin
    [Documentation]    Login as admin user using JavaScript where possible
    # Wait for login elements
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=15s
    
    # Enter credentials
    Input Text    ${USERNAME_INPUT}    ${ADMIN_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${PASSWORD}
    Capture Page Screenshot    before_login.png
    
    # Try to click the submit button, fallback to JavaScript if it fails
    ${click_success}=    Run Keyword And Return Status    Click Element    ${SUBMIT_BTN}
    
    # If click fails, try JavaScript submit
    Run Keyword If    not ${click_success}    Execute JavaScript    document.querySelector('form').submit();
    
    Sleep    5s
    Capture Page Screenshot    after_login.png
    Log    Logged in as admin

Navigate To Dashboard
    [Documentation]    Navigate to the dashboard page using JavaScript
    # Use JavaScript to navigate directly to dashboard to avoid click issues
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

Verify Recent Logs For HTTP 404
    [Documentation]    Check if HTTP 404 appears in the Action column of the most recent logs
    # Take screenshot before checking
    Capture Page Screenshot    before_verification.png
    
    # Try scrolling to where the logs table should be
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight * 0.6);
    Sleep    2s
    Capture Page Screenshot    scrolled_to_logs.png
    
    # Verify the table exists
    Wait Until Element Is Visible    ${LOG_TABLE}    timeout=10s
    
    # Check the latest row for HTTP 404
    ${latest_row_text}=    Get Text    ${LATEST_ROW}
    ${second_latest_row_text}=    Get Text    ${SECOND_LATEST_ROW}
    
    # Log the content of the latest rows for debugging
    Log    Latest row content: ${latest_row_text}
    Log    Second latest row content: ${second_latest_row_text}
    
    # Check if either of the top two rows contains HTTP 404
    ${latest_has_404}=    Run Keyword And Return Status    Should Contain    ${latest_row_text}    HTTP 404
    ${second_latest_has_404}=    Run Keyword And Return Status    Should Contain    ${second_latest_row_text}    HTTP 404
    
    # Check if either of the top rows contains our random URL segment
    ${latest_has_url}=    Run Keyword And Return Status    Should Contain    ${latest_row_text}    ${RANDOM_URL_SEGMENT}
    ${second_latest_has_url}=    Run Keyword And Return Status    Should Contain    ${second_latest_row_text}    ${RANDOM_URL_SEGMENT}
    
    # Log what we found
    Run Keyword If    ${latest_has_404}    Log    SUCCESS: Latest log entry contains HTTP 404
    Run Keyword If    ${second_latest_has_404}    Log    SUCCESS: Second latest log entry contains HTTP 404
    Run Keyword If    ${latest_has_url}    Log    SUCCESS: Latest log entry contains our URL segment
    Run Keyword If    ${second_latest_has_url}    Log    SUCCESS: Second latest log entry contains our URL segment
    
    # Take screenshot showing the results
    Capture Page Screenshot    verification_result.png
    
    # Check the action column specifically in the top rows
    ${latest_action_cell}=    Get Text    xpath=//table//tr[2]/td[count(//table//th[contains(text(),'Action')]/preceding-sibling::th)+1]
    ${second_latest_action_cell}=    Get Text    xpath=//table//tr[3]/td[count(//table//th[contains(text(),'Action')]/preceding-sibling::th)+1]
    
    Log    Latest row action: "${latest_action_cell}"
    Log    Second latest row action: "${second_latest_action_cell}"
    
    # Test passes if either of the top two rows has HTTP 404 in the Action column
    ${action_has_404}=    Evaluate    "HTTP 404" in """${latest_action_cell}""" or "HTTP 404" in """${second_latest_action_cell}"""
    Should Be True    ${action_has_404}    HTTP 404 not found in the Action column of the latest logs