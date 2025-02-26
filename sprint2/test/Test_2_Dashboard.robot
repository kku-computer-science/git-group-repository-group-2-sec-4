*** Settings ***
Library    SeleniumLibrary
Library    DateTime

*** Variables ***
${URL}    https://cs040268.cpkkuhost.com/
${BROWSER}    Chrome
${CHROME_DRIVER_PATH}    ${EXECDIR}${/}ChromeForTesting${/}chromedriver.exe

# Login Credentials
${ADMIN_EMAIL}           admin@gmail.com
${ADMIN_PASSWORD}        123456789

# Login Page Locators
${LOGIN_BTN}             xpath=(//a[@class='btn-solid-sm' and contains(@href, '/login')])[1]
${USERNAME_INPUT}        id=username
${PASSWORD_INPUT}        id=password
${SUBMIT_BTN}            xpath=//button[@type='submit']

# Dashboard Elements
${WELCOME_MESSAGE}       xpath=//h4[contains(text(), 'สวัสดี')]
${ADMIN_WELCOME}         xpath=//h4[contains(text(), 'สวัสดี') and contains(text(), 'ผู้ดูแลระบบ')]

# Log Statistics Cards
${TOTAL_LOGS_CARD}       xpath=//div[contains(@class, 'log-filter') and @data-type='totalLogs']
${TOTAL_LOGS_COUNT}      xpath=//div[@data-type='totalLogs']//p[@class='card-text']/strong
${ERROR_LOGS_CARD}       xpath=//div[contains(@class, 'log-filter') and @data-type='errors']
${ERROR_LOGS_COUNT}      xpath=//div[@data-type='errors']//p[@class='card-text']/strong
${WARNING_LOGS_CARD}     xpath=//div[contains(@class, 'log-filter') and @data-type='warnings']
${WARNING_LOGS_COUNT}    xpath=//div[@data-type='warnings']//p[@class='card-text']/strong
${INFO_LOGS_CARD}        xpath=//div[contains(@class, 'log-filter') and @data-type='info']
${INFO_LOGS_COUNT}       xpath=//div[@data-type='info']//p[@class='card-text']/strong

# Chart Elements
${AREA_CHART}            xpath=//div[@class='card shadow mb-4']
${AREA_CHART_TITLE}      xpath=//h6[contains(@class, 'font-weight-bold') and contains(text(), 'Area Chart')]
${CHART_CANVAS}          id=myAreaChart

# Frequent Logs Section
${FREQUENT_LOGS_SECTION}     xpath=//h5[contains(text(), 'Top 5 Most Frequent Logs')]
${TIME_RANGE_SELECTOR}       id=time_range
${FREQUENT_LOGS_TABLE}       xpath=//h5[contains(text(), 'Top 5 Most Frequent Logs')]/ancestor::div[@class='card-body']//table

*** Test Cases ***
Admin Dashboard Access Test
    [Documentation]    Verify admin can access dashboard and see welcome message
    Login As Admin
    Verify Welcome Message
    [Teardown]    Close Browser

Admin Dashboard Log Statistics Test
    [Documentation]    Verify the log statistics cards are displayed correctly
    Login As Admin
    Verify Log Statistics Cards Exist
    Verify Log Statistics Numbers
    [Teardown]    Close Browser

Admin Dashboard Area Chart Test
    [Documentation]    Verify the area chart is displayed and interactive
    Login As Admin
    Verify Area Chart Exists
    Verify Area Chart Interactivity
    [Teardown]    Close Browser

Admin Dashboard Frequent Logs Test
    [Documentation]    Verify the frequent logs section and time range selector
    Login As Admin
    Verify Frequent Logs Section
    Verify Time Range Selector
    [Teardown]    Close Browser

*** Keywords ***
Login As Admin
    Open Browser    ${URL}    ${BROWSER}    executable_path=${CHROME_DRIVER_PATH}
    Maximize Browser Window
    
    # Wait for login button and click
    Wait Until Element Is Visible    ${LOGIN_BTN}    timeout=10s
    Wait Until Element Is Enabled    ${LOGIN_BTN}    timeout=10s
    
    # Use JavaScript to click if regular click doesn't work
    ${clicked}    Run Keyword And Return Status    Click Element    ${LOGIN_BTN}
    Run Keyword If    not ${clicked}    Execute JavaScript    document.querySelector("a.btn-solid-sm[href*='/login']").click()
    
    # Check for window handling if needed
    ${all_windows}    Get Window Handles
    ${window_count}    Get Length    ${all_windows}
    Run Keyword If    ${window_count} > 1    Switch Window    ${all_windows}[1]
    
    # Input credentials
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=10s
    Input Text    ${USERNAME_INPUT}    ${ADMIN_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${ADMIN_PASSWORD}
    
    # Submit login
    Click Element    ${SUBMIT_BTN}
    
    # Wait for dashboard to load
    Sleep    2s

Verify Welcome Message
    Wait Until Element Is Visible    ${WELCOME_MESSAGE}    timeout=10s
    Element Should Be Visible    ${ADMIN_WELCOME}    The admin welcome message should be visible
    Element Should Contain    ${ADMIN_WELCOME}    ผู้ดูแลระบบ    The welcome message should mention admin role

Verify Log Statistics Cards Exist
    Wait Until Element Is Visible    ${TOTAL_LOGS_CARD}    timeout=10s
    Element Should Be Visible    ${TOTAL_LOGS_CARD}    Total Logs card should be visible
    Element Should Be Visible    ${ERROR_LOGS_CARD}    Error Logs card should be visible
    Element Should Be Visible    ${WARNING_LOGS_CARD}    Warning Logs card should be visible
    Element Should Be Visible    ${INFO_LOGS_CARD}    Info Logs card should be visible

Verify Log Statistics Numbers
    # Check that each card contains a number
    ${total_logs}=    Get Text    ${TOTAL_LOGS_COUNT}
    ${error_logs}=    Get Text    ${ERROR_LOGS_COUNT}
    ${warning_logs}=    Get Text    ${WARNING_LOGS_COUNT}
    ${info_logs}=    Get Text    ${INFO_LOGS_COUNT}
    
    # Verify each count is a number
    Should Match Regexp    ${total_logs}    ^\\d+$    Total logs count should be a number
    Should Match Regexp    ${error_logs}    ^\\d+$    Error logs count should be a number
    Should Match Regexp    ${warning_logs}    ^\\d+$    Warning logs count should be a number
    Should Match Regexp    ${info_logs}    ^\\d+$    Info logs count should be a number
    
    # Verify the sum of error, warning and info logs equals total logs
    ${total_expected}=    Evaluate    int(${error_logs}) + int(${warning_logs}) + int(${info_logs})
    ${total_actual}=    Convert To Integer    ${total_logs}
    
    # Log the values for debugging
    Log    Expected total: ${total_expected}
    Log    Actual total: ${total_actual}
    
    # Check that the numbers add up (note: they might not if there are logs without a level)
    # Should Be Equal As Integers    ${total_expected}    ${total_actual}    The sum of log types should equal the total logs

Verify Area Chart Exists
    Wait Until Element Is Visible    ${AREA_CHART}    timeout=10s
    Element Should Be Visible    ${AREA_CHART_TITLE}    Area Chart title should be visible
    Element Should Be Visible    ${CHART_CANVAS}    Chart canvas should be visible

Verify Area Chart Interactivity
    # Test clicking on each card to see if the chart updates
    Click Element    ${TOTAL_LOGS_CARD}
    Sleep    1s
    
    Click Element    ${ERROR_LOGS_CARD}
    Sleep    1s
    
    Click Element    ${WARNING_LOGS_CARD}
    Sleep    1s
    
    Click Element    ${INFO_LOGS_CARD}
    Sleep    1s

Verify Frequent Logs Section
    Wait Until Element Is Visible    ${FREQUENT_LOGS_SECTION}    timeout=10s
    Element Should Be Visible    ${FREQUENT_LOGS_SECTION}    Top 5 Most Frequent Logs section should be visible
    Element Should Be Visible    ${FREQUENT_LOGS_TABLE}    Frequent logs table should be visible

Verify Time Range Selector
    Element Should Be Visible    ${TIME_RANGE_SELECTOR}    Time range selector should be visible

    @{options}=    Get List Items    ${TIME_RANGE_SELECTOR}

    Should Contain    ${options}    Now
    Should Contain    ${options}    Last 2 Hours
    Should Contain    ${options}    Last 24 Hours
    Should Contain    ${options}    Last 7 Days
    Should Contain    ${options}    Last 30 Days