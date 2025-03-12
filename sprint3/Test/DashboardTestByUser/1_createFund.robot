*** Settings ***
Documentation     Test teacher login and create new fund
Library           SeleniumLibrary

*** Variables ***
${URL}                    https://cs040268.cpkkuhost.com
${LOGIN_URL}              ${URL}/login
${BROWSER}                Chrome
${USERNAME_INPUT}         xpath=//input[@placeholder='USERNAME' or contains(@name, 'username')]
${PASSWORD_INPUT}         xpath=//input[@placeholder='PASSWORD' or contains(@name, 'password') or @type='password']
${SUBMIT_BTN}             xpath=//button[contains(text(), 'LOG IN') or @type='submit']
${MANAGE_FUND_LINK}       xpath=//a[contains(text(), 'Manage Fund')] | //a[contains(@href, 'funds')]
${ADD_FUND_BTN}           xpath=//button[contains(text(), 'ADD')] | //a[contains(text(), 'ADD')]

# More flexible form field locators
${FUND_TYPE_DROPDOWN}     xpath=//*[contains(text(), 'ประเภททุนวิจัย')]/following::select[1] | //*[contains(text(), 'ประเภททุนวิจัย')]/following::*[contains(@class, 'select') or contains(@class, 'dropdown')][1]
${FUND_LEVEL_DROPDOWN}    xpath=//*[contains(text(), 'ระดับทุน')]/following::select[1] | //*[contains(text(), 'ระดับทุน')]/following::*[contains(@class, 'select') or contains(@class, 'dropdown')][1]
${FUND_NAME_INPUT}        xpath=//*[contains(text(), 'ชื่อทุน')]/following::input[1]
${SUPPORTING_DEPT_INPUT}  xpath=//*[contains(text(), 'หน่วยงานที่')]/following::input[1]
${FORM_SUBMIT_BTN}        xpath=//button[contains(text(), 'Submit')]

# Form Data
${TEACHER_EMAIL}          jonathan.teacher@gmail.com
${PASSWORD}               123456789
${FUND_NAME}              New Funds
${SUPPORTING_DEPT}        KKU

*** Test Cases ***
Teacher Login And Create New Fund
    [Documentation]    Login as teacher, navigate to Manage Fund, and create a new fund
    [Timeout]          3 minutes
    
    Open Browser    ${LOGIN_URL}    ${BROWSER}
    Maximize Browser Window
    
    # Login
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=10s
    Input Text    ${USERNAME_INPUT}    ${TEACHER_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${PASSWORD}
    Click Element    ${SUBMIT_BTN}
    Wait Until Location Contains    dashboard    timeout=10s
    
    # Navigate to Manage Fund
    Wait Until Element Is Visible    ${MANAGE_FUND_LINK}    timeout=10s
    Click Element    ${MANAGE_FUND_LINK}
    Wait Until Location Contains    funds    timeout=10s
    
    # Click Add button
    Wait Until Element Is Visible    ${ADD_FUND_BTN}    timeout=10s
    Click Element    ${ADD_FUND_BTN}
    Wait Until Location Contains    funds/create    timeout=10s
    Capture Page Screenshot    before_filling_form.png
    
    # For debugging - log the page source to see the actual HTML structure
    ${page_source}=    Get Source
    Log    ${page_source}
    
    # Fill out the fund creation form with more robust handling
    # Handle fund type selection - try multiple approaches
    ${fund_type_visible}=    Run Keyword And Return Status    Element Should Be Visible    ${FUND_TYPE_DROPDOWN}
    Run Keyword If    ${fund_type_visible}    Select From List By Label    ${FUND_TYPE_DROPDOWN}    ทุนภายใน
    Run Keyword If    not ${fund_type_visible}    Execute JavaScript    document.querySelector('select, [role="combobox"]').value = 'ทุนภายใน'
    
    # Handle fund level selection - try multiple approaches
    ${fund_level_visible}=    Run Keyword And Return Status    Element Should Be Visible    ${FUND_LEVEL_DROPDOWN}
    Run Keyword If    ${fund_level_visible}    Select From List By Label    ${FUND_LEVEL_DROPDOWN}    กลาง
    Run Keyword If    not ${fund_level_visible}    Execute JavaScript    document.querySelectorAll('select, [role="combobox"]')[1].value = 'กลาง'
    
    # Input fund name
    Wait Until Element Is Visible    ${FUND_NAME_INPUT}    timeout=10s
    Input Text    ${FUND_NAME_INPUT}    ${FUND_NAME}
    
    # Input supporting department
    Wait Until Element Is Visible    ${SUPPORTING_DEPT_INPUT}    timeout=10s
    Input Text    ${SUPPORTING_DEPT_INPUT}    ${SUPPORTING_DEPT}
    
    Capture Page Screenshot    after_filling_form.png
    
    # Submit the form
    Click Element    ${FORM_SUBMIT_BTN}
    
    # Verify successful submission
    Wait Until Location Contains    funds    timeout=10s
    Wait Until Page Does Not Contain    funds/create    timeout=10s
    Capture Page Screenshot    after_submission.png
    
    [Teardown]    Close Browser