*** Settings ***
Library    SeleniumLibrary

*** Variables ***
${URL}    https://cs040268.cpkkuhost.com/
${BROWSER}    Chrome
${CHROME_DRIVER_PATH}    ${EXECDIR}${/}ChromeForTesting${/}chromedriver.exe

${HOME_TEXT}             xpath=//a[@class='nav-link' and text()='Home']
${LOGIN_BTN}             xpath=(//a[@class='btn-solid-sm' and contains(@href, '/login')])[1]
${USERNAME_INPUT}        id=username
${PASSWORD_INPUT}        id=password
${SUBMIT_BTN}            xpath=//button[@type='submit']

${TOTAL_LOGS_CARD}       xpath=//div[contains(@class, 'log-filter') and @data-type='totalLogs']
${ERROR_LOGS_CARD}       xpath=//div[contains(@class, 'log-filter') and @data-type='errors']
${WARNING_LOGS_CARD}     xpath=//div[contains(@class, 'log-filter') and @data-type='warnings']
${INFO_LOGS_CARD}        xpath=//div[contains(@class, 'log-filter') and @data-type='info']

${STUDENT_EMAIL}         jonathan.student@gmail.com
${TEACHER_EMAIL}         jonathan.teacher@gmail.com
${STAFF_EMAIL}           jonathan.staff@gmail.com
${ADMIN_EMAIL}           admin@gmail.com
${PASSWORD}              123456789

*** Test Cases ***
Test Student Does Not See Log Dashboard
    [Documentation]    ตรวจสอบว่า Student ไม่เห็น Dashboard ของ Total Logs, Errors, Warnings, Info
    Check Log Dashboard Access    ${STUDENT_EMAIL}    ${PASSWORD}    should_not_see

Test Teacher Does Not See Log Dashboard
    [Documentation]    ตรวจสอบว่า Teacher ไม่เห็น Dashboard ของ Total Logs, Errors, Warnings, Info
    Check Log Dashboard Access    ${TEACHER_EMAIL}    ${PASSWORD}    should_not_see

Test Staff Does Not See Log Dashboard
    [Documentation]    ตรวจสอบว่า Staff ไม่เห็น Dashboard ของ Total Logs, Errors, Warnings, Info
    Check Log Dashboard Access    ${STAFF_EMAIL}    ${PASSWORD}    should_not_see

Test Admin Can See Log Dashboard
    [Documentation]    ตรวจสอบว่า Admin เห็น Dashboard ของ Total Logs, Errors, Warnings, Info
    Check Log Dashboard Access    ${ADMIN_EMAIL}    ${PASSWORD}    should_see

*** Keywords ***
Check Log Dashboard Access
    [Arguments]    ${email}    ${password}    ${expectation}

    Open Browser    ${URL}    ${BROWSER}    executable_path=${CHROME_DRIVER_PATH}
    Maximize Browser Window

    Wait Until Element Is Visible    ${HOME_TEXT}    timeout=10s
    Element Should Be Visible    ${HOME_TEXT}    Home Text should be visible

    Wait Until Element Is Visible    ${LOGIN_BTN}    timeout=10s
    Wait Until Element Is Enabled    ${LOGIN_BTN}    timeout=10s

    ${clicked}    Run Keyword And Return Status    Click Element    ${LOGIN_BTN}
    Run Keyword If    not ${clicked}    Execute JavaScript    document.querySelector("a.btn-solid-sm[href*='/login']").click()

    ${all_windows}    Get Window Handles
    ${window_count}    Get Length    ${all_windows}
    Run Keyword If    ${window_count} > 1    Switch Window    ${all_windows}[1]

    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=10s
    Input Text    ${USERNAME_INPUT}    ${email}
    Input Text    ${PASSWORD_INPUT}    ${password}

    Click Element    ${SUBMIT_BTN}
    
    
    Sleep    2s
    
    ${current_url}    Get Location
    Log To Console    Current URL: ${current_url}
    
    ${total_logs_visible}    Run Keyword And Return Status    Element Should Be Visible    ${TOTAL_LOGS_CARD}
    ${error_logs_visible}    Run Keyword And Return Status    Element Should Be Visible    ${ERROR_LOGS_CARD}
    ${warning_logs_visible}    Run Keyword And Return Status    Element Should Be Visible    ${WARNING_LOGS_CARD}
    ${info_logs_visible}    Run Keyword And Return Status    Element Should Be Visible    ${INFO_LOGS_CARD}
    

    Run Keyword If    '${expectation}'=='should_see'    Run Keywords
    ...    Should Be True    ${total_logs_visible}    Total Logs dashboard should be visible for ${email}    AND
    ...    Should Be True    ${error_logs_visible}    Error Logs dashboard should be visible for ${email}    AND
    ...    Should Be True    ${warning_logs_visible}    Warning Logs dashboard should be visible for ${email}    AND
    ...    Should Be True    ${info_logs_visible}    Info Logs dashboard should be visible for ${email}
    
    Run Keyword If    '${expectation}'=='should_not_see'    Run Keywords
    ...    Should Not Be True    ${total_logs_visible}    Total Logs dashboard should not be visible for ${email}    AND
    ...    Should Not Be True    ${error_logs_visible}    Error Logs dashboard should not be visible for ${email}    AND
    ...    Should Not Be True    ${warning_logs_visible}    Warning Logs dashboard should not be visible for ${email}    AND
    ...    Should Not Be True    ${info_logs_visible}    Info Logs dashboard should not be visible for ${email}

    Close Browser