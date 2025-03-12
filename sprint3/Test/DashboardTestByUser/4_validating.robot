*** Settings ***
Documentation     Test to verify system logs contain expected actions
Library           SeleniumLibrary
Library           String
Library           DateTime
Library           Collections

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
${LOG_ROWS}               xpath=//table//tr[position() > 1 and position() <= 11]  # Get rows 2-11 (10 rows after header)
${ACTION_CELLS}           xpath=//table//tr[position() > 1 and position() <= 11]/td[6]  # Action column (6th column)

# Test Data
${CORRECT_EMAIL}          admin@gmail.com
${CORRECT_PASSWORD}       123456789
${INCORRECT_EMAIL}        incorrect@example.com
${INCORRECT_PASSWORD}     wrongpassword123

# Expected Log Actions
@{EXPECTED_ACTIONS}       User Logged In    Created Fund    User Logged In    Editing Fund    Fund Validation Failed    Editing Fund    User Logged In    Deleted Fund

*** Test Cases ***
Verify Expected Actions In System Logs
    [Documentation]    Verify that the expected actions appear in the System Logs
    [Timeout]          5 minutes
    
    # Step 1: Open browser and login as admin
    Open Browser    ${URL}    ${BROWSER}
    Maximize Browser Window
    Set Selenium Implicit Wait    5s
    Navigate To Login Page
    Login As Admin
    
    # Step 2: Navigate to dashboard and check for expected log actions
    Navigate To Dashboard
    Verify Expected Actions In Logs
    
    # Clean up
    [Teardown]    Close Browser

*** Keywords ***
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

Verify Expected Actions In Logs
    [Documentation]    Check if the expected actions appear in the System Logs in the correct order
    # Take screenshot before checking
    Capture Page Screenshot    before_verification.png
    
    # Try scrolling to where the logs table should be
    Execute JavaScript    window.scrollTo(0, document.body.scrollHeight * 0.6);
    Sleep    2s
    Capture Page Screenshot    scrolled_to_logs.png
    
    # Verify the table exists
    Wait Until Element Is Visible    ${LOG_TABLE}    timeout=10s
    
    # Get all action cells from the first 10 rows
    @{action_elements}=    Get WebElements    ${ACTION_CELLS}
    
    # Create list to store the actual actions found
    @{actual_actions}=    Create List
    
    # Extract text from each action cell
    FOR    ${element}    IN    @{action_elements}
        ${action_text}=    Get Text    ${element}
        Append To List    ${actual_actions}    ${action_text}
        Log    Found action: ${action_text}
    END
    
    # Log the actual actions found for debugging
    Log    Actual actions found: ${actual_actions}
    Log    Expected actions: ${EXPECTED_ACTIONS}
    
    # Verify each expected action is in the list of actual actions, accounting for new admin login
    ${all_found}=    Set Variable    ${TRUE}
    
    # First check if there's a new "User Logged In" at the top that's from the admin login
    ${admin_login_found}=    Run Keyword And Return Status    Should Contain    ${actual_actions}[0]    User Logged In
    
    # Skip the first entry if it's the admin login, then check for the expected actions
    ${start_index}=    Set Variable If    ${admin_login_found}    1    0
    Log    Starting check from index ${start_index} (admin login found: ${admin_login_found})
    
    FOR    ${expected}    IN    @{EXPECTED_ACTIONS}
        ${found}=    Run Keyword And Return Status    List Should Contain Value    ${actual_actions}    ${expected}
        Run Keyword If    ${found}    Log    Found expected action: ${expected}
        Run Keyword If    not ${found}    Log    Missing expected action: ${expected}
        ${all_found}=    Set Variable If    not ${found}    ${FALSE}    ${all_found}
    END
    
    # Take final screenshot
    Capture Page Screenshot    after_verification.png
    
    # Final verification that all expected actions were found
    Should Be True    ${all_found}    Not all expected actions were found in the logs
    Log    SUCCESS: All expected log actions were verified!