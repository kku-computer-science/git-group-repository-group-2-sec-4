*** Settings ***
Library    SeleniumLibrary

*** Variables ***
${URL}    http://127.0.0.1:8000/
${BROWSER}    Chrome
${CHROME_DRIVER_PATH}    ${EXECDIR}${/}ChromeForTesting${/}chromedriver.exe

# Locators
${HOME_TEXT}             xpath=//a[@class='nav-link' and text()='Home']
${LOGIN_BTN}             xpath=(//a[@class='btn-solid-sm' and contains(@href, '/login')])[1]
${USERNAME_INPUT}        id=username
${PASSWORD_INPUT}        id=password
${SUBMIT_BTN}            xpath=//button[@type='submit']
${SYSTEM_LOG_MENU}       xpath=//span[contains(text(), 'System Logs')]

# Test Data
${STUDENT_EMAIL}         jonathan.student@gmail.com
${TEACHER_EMAIL}         jonathan.teacher@gmail.com
${STAFF_EMAIL}           jonathan.staff@gmail.com
${ADMIN_EMAIL}           admin@gmail.com
${PASSWORD}              123456789

*** Test Cases ***
Test Student Home And System Log
    [Documentation]    ตรวจสอบว่า Student เห็น Home Text และไม่เห็น System Log
    Check Home Text And Login    ${STUDENT_EMAIL}    ${PASSWORD}    should_not_see

Test Teacher Home And System Log
    [Documentation]    ตรวจสอบว่า Teacher เห็น Home Text และไม่เห็น System Log
    Check Home Text And Login    ${TEACHER_EMAIL}    ${PASSWORD}    should_not_see

Test Staff Home And System Log
    [Documentation]    ตรวจสอบว่า Staff เห็น Home Text และไม่เห็น System Log
    Check Home Text And Login    ${STAFF_EMAIL}    ${PASSWORD}    should_not_see

Test Admin Home And System Log
    [Documentation]    ตรวจสอบว่า Admin เห็น Home Text และเห็น System Log
    Check Home Text And Login    ${ADMIN_EMAIL}    ${PASSWORD}    should_see

*** Keywords ***
Check Home Text And Login
    [Arguments]    ${email}    ${password}    ${expectation}

    # เปิดเบราว์เซอร์และเข้าไปที่หน้าแรก
    Open Browser    ${URL}    ${BROWSER}    executable_path=${CHROME_DRIVER_PATH}
    Maximize Browser Window

    # ตรวจสอบว่าเห็น Home Text
    Wait Until Element Is Visible    ${HOME_TEXT}    timeout=10s
    Element Should Be Visible    ${HOME_TEXT}    Home Text should be visible

    # รอให้ปุ่ม Login พร้อมก่อนคลิก
    Wait Until Element Is Visible    ${LOGIN_BTN}    timeout=10s
    Wait Until Element Is Enabled    ${LOGIN_BTN}    timeout=10s

    # ใช้ JavaScript คลิกถ้าคลิกปกติไม่ได้
    ${clicked}    Run Keyword And Return Status    Click Element    ${LOGIN_BTN}
    Run Keyword If    not ${clicked}    Execute JavaScript    document.querySelector("a.btn-solid-sm[href*='/login']").click()

    # ตรวจสอบว่ามีการเปิดหน้าต่างใหม่หรือไม่
    ${all_windows}    Get Window Handles
    ${window_count}    Get Length    ${all_windows}
    Run Keyword If    ${window_count} > 1    Switch Window    ${all_windows}[1]

    # กรอกข้อมูล Username และ Password
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=10s
    Input Text    ${USERNAME_INPUT}    ${email}
    Input Text    ${PASSWORD_INPUT}    ${password}

    # กดปุ่ม Log In
    Click Element    ${SUBMIT_BTN}

    # ตรวจสอบการแสดงผลของเมนู System Log ตามสิทธิ์ผู้ใช้
    ${is_visible}    Run Keyword And Return Status    Wait Until Element Is Visible    ${SYSTEM_LOG_MENU}    timeout=5s

    Run Keyword If    '${expectation}'=='should_see'    Should Be True    ${is_visible}    System Log menu should be visible for ${email}
    Run Keyword If    '${expectation}'=='should_not_see'    Should Not Be True    ${is_visible}    System Log menu should not be visible for ${email}

    Close Browser
