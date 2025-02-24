*** Settings ***
Library    SeleniumLibrary

*** Variables ***
${URL}    http://127.0.0.1:8000/
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

    # ดึงหัวข้อ h5 และปุ่ม More details ทั้งหมด
    ${sections}    Get WebElements    xpath=//h5
    ${buttons}    Get WebElements    ${MORE_DETAILS_BTN}

    # ตรวจสอบว่ามีหัวข้อและปุ่มอยู่
    Should Not Be Empty    ${sections}    "No research groups found!"
    Should Not Be Empty    ${buttons}    "No 'More details' buttons found!"

    ${section_count}    Get Length    ${sections}
    ${button_count}    Get Length    ${buttons}
    Should Be Equal As Integers    ${section_count}    ${button_count}    "Mismatch between sections and buttons!"

    # วนลูปตรวจสอบแต่ละหัวข้อ
    FOR    ${index}    IN RANGE    ${section_count}
        ${title}    Get Text    ${sections}[${index}]
        Log To Console    Checking research group: ${title}

        ${more_button}    Set Variable    ${buttons}[${index}]
        Scroll Element Into View    ${more_button}
        Wait Until Element Is Visible    ${more_button}    timeout=5s
        Click Element    ${more_button}

        # รอให้หน้าถัดไปโหลด
        Run Keyword And Continue On Failure    Wait Until Page Contains Element    xpath=//h1|//h2|//h3|//h4|//h5    timeout=10s
        Run Keyword And Continue On Failure    Wait Until Page Contains    ${title}    timeout=10s
        Run Keyword And Continue On Failure    Capture Page Screenshot    

        # กลับไปหน้าหลัก Research Group
        Go Back
        Wait Until Element Is Visible    ${RESEARCH_GROUP}    timeout=10s
    END

    Close Browser
