*** Settings ***
Documentation     Test to verify "User Logged In" appears in latest System Logs action column
Library           SeleniumLibrary
Library           String
Library           DateTime

*** Variables ***
${URL}                    https://cs040268.cpkkuhost.com
${LOGIN_URL}              ${URL}/login
${ADMIN_URL}              ${URL}/admin
${SYSTEM_LOGS_URL}        ${URL}/dashboard
${BROWSER}                Chrome

# Login Page Locators
${USERNAME_INPUT}         xpath=//input[@placeholder='USERNAME' or contains(@name, 'username')]
${PASSWORD_INPUT}         xpath=//input[@placeholder='PASSWORD' or contains(@name, 'password') or @type='password']
${SUBMIT_BTN}             xpath=//button[contains(text(), 'LOG IN') or @type='submit']

# System Logs Page Locators 
${SYSTEM_LOGS_LINK}       xpath=//a[contains(text(),'System Logs')]
${LOG_TABLE}              xpath=//table[contains(@class,'table-striped')]
${TABLE_BODY}             xpath=//table[contains(@class,'table-striped')]//tbody
${LATEST_ACTION_CELL}     xpath=//table//tbody/tr[1]/td[6]
${LATEST_LOGIN_ACTION}    xpath=//table//tbody/tr[1][td[6][contains(text(),'User Logged In')]]

# Test Data
${ADMIN_EMAIL}            admin@gmail.com
${PASSWORD}               123456789

*** Test Cases ***
Verify User Logged In In Latest System Log
    [Documentation]    Verify that "User Logged In" appears in the latest System Log action column
    [Timeout]          5 minutes
    
    # Step 1: Open browser
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Set Selenium Implicit Wait    5s
    
    # Step 2: Login as admin
    Navigate To Login Page
    Login As Admin
    
    # Step 3: Navigate to System Logs and check for admin login
    Navigate To System Logs
    Check Latest Log For User Logged In
    
    # Clean up
    [Teardown]    Close Browser

*** Keywords ***
Navigate To Login Page
    [Documentation]    Navigate directly to the login page
    Go To    ${LOGIN_URL}
    Sleep    3s
    Capture Page Screenshot    login_page.png
    
    # Check if login elements are visible
    ${username_visible}=    Run Keyword And Return Status    Element Should Be Visible    ${USERNAME_INPUT}
    Run Keyword If    not ${username_visible}    Reload Page
    Run Keyword If    not ${username_visible}    Sleep    2s

Login As Admin
    [Documentation]    Login as admin user
    # Wait for login elements
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=15s
    
    # Enter credentials
    Input Text    ${USERNAME_INPUT}    ${ADMIN_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${PASSWORD}
    Capture Page Screenshot    before_login.png
    
    # Submit form
    Click Element    ${SUBMIT_BTN}
    Sleep    5s
    Capture Page Screenshot    after_login.png
    Log    Logged in as admin

Navigate To System Logs
    [Documentation]    Navigate to the System Logs page
    # Go directly to the dashboard page which contains the system logs
    Go To    ${SYSTEM_LOGS_URL}
    Sleep    5s
    
    # Scroll down to make sure we can see the table
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight/2);
    Sleep    2s
    
    Capture Page Screenshot    system_logs_top.png
    
    # Scroll down to the bottom to capture the table
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight);
    Sleep    2s
    
    Capture Page Screenshot    system_logs_bottom.png
    
    # Check if we're on the correct page
    Wait Until Page Contains    System Logs    timeout=10s
    Log    Successfully navigated to System Logs page

Check Latest Log For User Logged In
    [Documentation]    Check the latest log entry for "User Logged In" in Action column
    # Check if log table is visible
    Wait Until Page Contains Element    ${LOG_TABLE}    timeout=10s
    
    # Use built-in scrolling keyword
    Scroll Element Into View    ${LOG_TABLE}
    Sleep    2s
    
    # Take screenshot of the table
    Capture Page Screenshot    table_visible.png
    
    # Check the latest entry
    Check Latest Action For Login
    
    # Take screenshot of final state
    Capture Page Screenshot    final_check.png

Check Latest Action For Login
    [Documentation]    Check if the latest action cell contains "User Logged In"
    # Wait for the table to be fully loaded
    Wait Until Element Is Visible    ${LOG_TABLE}    timeout=10s
    
    # Use built-in scrolling keyword
    Scroll Element Into View    ${TABLE_BODY}
    Sleep    2s
    
    # Take screenshot focusing on the first row
    Capture Page Screenshot    table_first_row.png
    
    # Get text of the latest action cell (6th column in first row)
    Wait Until Element Is Visible    ${LATEST_ACTION_CELL}    timeout=10s
    
    # Use built-in scrolling keyword
    Scroll Element Into View    ${LATEST_ACTION_CELL}
    Sleep    1s
    
    # Take screenshot focused on the action cell
    Capture Page Screenshot    action_cell.png
    
    ${latest_action_text}=    Get Text    ${LATEST_ACTION_CELL}
    
    # Check if it contains "User Logged In"
    ${contains_login}=    Run Keyword And Return Status    Should Contain    ${latest_action_text}    User Logged In
    
    # Log the results
    Run Keyword If    ${contains_login}    Log    SUCCESS: Latest action contains "User Logged In"
    Run Keyword If    not ${contains_login}    Log    ERROR: Latest action does not contain "User Logged In". Found: ${latest_action_text}
    
    # Take screenshots
    Capture Page Screenshot    latest_action_check.png
    
    # Display the content of the latest action for debugging
    Log    Latest action text: ${latest_action_text}
    
    # Assert the result
    Should Be True    ${contains_login}    Latest action does not contain "User Logged In"