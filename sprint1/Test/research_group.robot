*** Settings ***
Library    SeleniumLibrary

*** Variables ***
${URL}    http://10.199.66.67/
${BROWSER}    Chrome
${CHROME_DRIVER_PATH}    ${EXECDIR}${/}ChromeForTesting${/}chromedriver.exe
${RESEARCH_GROUP}    xpath=//a[@class='nav-link' and contains(@href, '/researchgroup')]
${MORE_DETAILS_BTN}    xpath=//a[contains(@class, 'btn') and contains(text(), 'More details')]

*** Test Cases ***
Verify All Research Group Sections And More Details
    Open Browser    ${URL}    ${BROWSER}    executable_path=${CHROME_DRIVER_PATH}
    Maximize Browser Window

    # คลิกไปที่ Research Group
    Wait Until Element Is Visible    ${RESEARCH_GROUP}    timeout=10s
    Click Element    ${RESEARCH_GROUP}

    # ดึง div ทั้งหมดที่เป็น card-body
    ${divs}    Get WebElements    xpath=//div[contains(@class, 'card-body')]
    Should Not Be Empty    ${divs}    "No sections found!"
    
    # วนลูปตรวจสอบแต่ละ div ว่ามี h2, h3, h5 หรือไม่
    FOR    ${div}    IN    @{divs}
        ${h2_elements}    Get WebElements    xpath=.//h2[contains(@class, 'card-text-2')]
        ${h3_elements}    Get WebElements    xpath=.//h3
        ${h5_elements}    Get WebElements    xpath=.//h5

        ${h2_text}    Run Keyword If    ${h2_elements}    Get Text    ${h2_elements}[0]    ELSE    Set Variable    "No h2 found"
        ${h3_text}    Run Keyword If    ${h3_elements}    Get Text    ${h3_elements}[0]    ELSE    Set Variable    "No h3 found"
        ${h5_text}    Run Keyword If    ${h5_elements}    Get Text    ${h5_elements}[0]    ELSE    Set Variable    "No h5 found"
        
        Log To Console    Div found:
        Log To Console    - h2: ${h2_text}
        Log To Console    - h3: ${h3_text}
        Log To Console    - h5: ${h5_text}
    END
    
    Close Browser
