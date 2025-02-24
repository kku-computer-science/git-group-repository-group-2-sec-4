*** Settings ***
# เรียกใช้ Library สำหรับ Selenium เพื่อใช้ในการทดสอบ Web UI
Library    SeleniumLibrary

*** Variables ***
# กำหนดตัวแปรสำหรับ URL ของเว็บที่จะทดสอบ และ Browser ที่จะใช้
${URL}    http://127.0.0.1:8000/
${BROWSER}    Chrome
${CHROME_DRIVER_PATH}    ${EXECDIR}${/}ChromeForTesting${/}chromedriver.exe

# Locators: ตัวแปรระบุตำแหน่งของ Element ในหน้าเว็บ (XPath, ID)
${HOME_TEXT}               xpath=//a[@class='nav-link' and text()='Home']
${LOGIN_BTN}               xpath=(//a[@class='btn-solid-sm' and contains(@href, '/login')])[1]
${USERNAME_INPUT}          id=username
${PASSWORD_INPUT}          id=password
${SUBMIT_BTN}              xpath=//button[@type='submit']
${SYSTEM_LOG_MENU}         xpath=//a[@class='nav-link' and contains(., 'System Logs')]
${LOG_TABLE_ROWS}          xpath=//tbody/tr

# Test Data: ข้อมูลทดสอบสำหรับการเข้าสู่ระบบ
${ADMIN_EMAIL}             admin@gmail.com
${PASSWORD}                123456789

*** Test Cases ***
# Test Case หลัก: ทดสอบหน้า Home และ System Log ของ Admin
Test Admin Home And System Log
    [Documentation]    ตรวจสอบว่า Admin เห็น Home Text, เข้าถึง System Log Menu และมีข้อมูลในตาราง
    # เรียกใช้ Keyword สำหรับตรวจสอบ Home Text และเข้าสู่ระบบด้วยสิทธิ์ Admin
    Check Home Text And Login As Admin
    # เรียกใช้ Keyword สำหรับตรวจสอบข้อมูลใน System Logs
    Check System Logs

*** Keywords ***
# Keyword สำหรับตรวจสอบ Home Text และเข้าสู่ระบบในฐานะ Admin
Check Home Text And Login As Admin
    [Arguments]    ${email}=${ADMIN_EMAIL}    ${password}=${PASSWORD}

    # เปิด Browser และไปที่หน้า Home ของเว็บที่ต้องการทดสอบ
    Open Browser    ${URL}    ${BROWSER}    executable_path=${CHROME_DRIVER_PATH}
    Maximize Browser Window

    # ตรวจสอบว่า Home Text ปรากฏขึ้นหรือไม่
    Wait Until Element Is Visible    ${HOME_TEXT}    timeout=10s
    Element Should Be Visible    ${HOME_TEXT}    Home Text should be visible

    # ตรวจสอบและคลิกปุ่ม Login
    Wait Until Element Is Visible    ${LOGIN_BTN}    timeout=10s
    Wait Until Element Is Enabled    ${LOGIN_BTN}    timeout=10s
    Click Element    ${LOGIN_BTN}

    # ตรวจสอบว่ามีการเปิดหน้าต่างใหม่หรือไม่ และสลับไปยังหน้าต่างนั้น
    ${all_windows}    Get Window Handles
    ${window_count}    Get Length    ${all_windows}
    Run Keyword If    ${window_count} > 1    Switch Window    ${all_windows}[1]

    # กรอกข้อมูล Username และ Password
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=10s
    Input Text    ${USERNAME_INPUT}    ${email}
    Input Text    ${PASSWORD_INPUT}    ${password}

    # คลิกปุ่ม Log In เพื่อเข้าสู่ระบบ
    Click Element    ${SUBMIT_BTN}

    # ตรวจสอบว่าเมนู System Logs ปรากฏขึ้นหรือไม่
    Wait Until Element Is Visible    ${SYSTEM_LOG_MENU}    timeout=10s
    Element Should Be Visible    ${SYSTEM_LOG_MENU}    System Log menu should be visible for admin

    # คลิกที่เมนู System Logs โดยใช้ JavaScript Click เพื่อหลีกเลี่ยงปัญหาจาก JavaScript Framework
    Execute JavaScript    document.evaluate("//a[contains(., 'System Logs')]", document, null, XPathResult.FIRST_ORDERED_NODE_TYPE, null).singleNodeValue.click();

# Keyword สำหรับตรวจสอบข้อมูลใน System Logs
Check System Logs
    [Documentation]    ตรวจสอบว่ามี Log แสดงอยู่ในตาราง System Logs และข้อมูลถูกต้อง

    # ตรวจสอบว่ามีข้อมูลในตารางหรือไม่
    Wait Until Element Is Visible    ${LOG_TABLE_ROWS}    timeout=10s
    ${log_count}    Get Element Count    ${LOG_TABLE_ROWS}
    Should Be True    ${log_count} > 0    System Logs should not be empty

    # ตรวจสอบว่า Log ID ในแถวแรกไม่ว่างเปล่า
    ${log_id}    Get Text    xpath=(//tbody/tr[1]/td[1])
    Should Not Be Empty    ${log_id}    Log ID should not be empty

    # ตรวจสอบว่า Action ในแถวแรกไม่ว่างเปล่า
    ${action}    Get Text    xpath=(//tbody/tr[1]/td[5])
    Should Not Be Empty    ${action}    Action should not be empty
