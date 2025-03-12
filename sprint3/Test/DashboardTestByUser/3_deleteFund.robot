*** Settings ***
Documentation     Test fund deletion
Library           SeleniumLibrary

*** Variables ***
${URL}                    https://cs040268.cpkkuhost.com
${LOGIN_URL}              ${URL}/login
${BROWSER}                Chrome
${USERNAME_INPUT}         xpath=//input[@placeholder='USERNAME' or contains(@name, 'username')]
${PASSWORD_INPUT}         xpath=//input[@placeholder='PASSWORD' or contains(@name, 'password') or @type='password']
${SUBMIT_BTN}             xpath=//button[contains(text(), 'LOG IN') or @type='submit']
${MANAGE_FUND_LINK}       xpath=//a[contains(text(), 'Manage Fund')] | //a[contains(@href, 'funds')]

# Delete button in the table row
${DELETE_BTN}             xpath=//tr[contains(.,'New Funds')]//button[contains(@class,'btn-outline-danger')]

# Success dialog and confirmation buttons
${DELETE_CONFIRM_BTN}     xpath=//button[contains(@class,'swal-button--confirm')]
${SUCCESS_OK_BTN}         xpath=//div[contains(@class,'swal-overlay--show-modal')]//button[contains(text(),'OK')]

# Test Data
${TEACHER_EMAIL}          jonathan.teacher@gmail.com
${PASSWORD}               123456789
${FUND_NAME}              New Funds

*** Test Cases ***
Test Fund Delete
    [Documentation]    Log in as teacher and delete a fund
    [Timeout]          5 minutes
    
    Open Browser    ${LOGIN_URL}    ${BROWSER}
    Maximize Browser Window
    Set Selenium Speed    0.5s
    
    # Login
    Wait Until Element Is Visible    ${USERNAME_INPUT}    timeout=15s
    Input Text    ${USERNAME_INPUT}    ${TEACHER_EMAIL}
    Input Text    ${PASSWORD_INPUT}    ${PASSWORD}
    Click Element    ${SUBMIT_BTN}
    Wait Until Location Contains    dashboard    timeout=15s
    
    # Navigate to Manage Fund
    Wait Until Element Is Visible    ${MANAGE_FUND_LINK}    timeout=15s
    Click Element    ${MANAGE_FUND_LINK}
    Wait Until Location Contains    funds    timeout=15s
    
    # Wait for table to load
    Wait Until Page Contains    ${FUND_NAME}    timeout=15s
    Sleep    2s
    Capture Page Screenshot    before_delete.png
    
    # Click the delete button for the target fund using JavaScript
    Execute JavaScript    
    ...    var rows = document.querySelectorAll('tr');
    ...    for(var i=0; i<rows.length; i++) {
    ...        if(rows[i].textContent.includes('${FUND_NAME}')) {
    ...            var deleteBtn = rows[i].querySelector('button.btn-outline-danger, button[title="Delete"]');
    ...            if(deleteBtn) {
    ...                deleteBtn.click();
    ...                return true;
    ...            }
    ...        }
    ...    }
    ...    return false;
    
    # Wait for and handle the "Are you sure?" confirmation dialog
    Wait Until Page Contains    Are you sure?    timeout=10s
    Capture Page Screenshot    confirmation_dialog.png
    
    # Click the confirm/OK button in the first dialog
    Wait Until Element Is Visible    ${DELETE_CONFIRM_BTN}    timeout=5s
    Click Element    ${DELETE_CONFIRM_BTN}
    
    # Wait for the success dialog to appear
    Wait Until Page Contains    Delete Successfully    timeout=10s
    Capture Page Screenshot    success_dialog.png
    
    # Click the OK button in the success dialog
    Wait Until Element Is Visible    ${SUCCESS_OK_BTN}    timeout=5s
    Click Element    ${SUCCESS_OK_BTN}
    
    # After clicking OK on success dialog, wait to ensure the page refreshes
    Sleep    3s
    Capture Page Screenshot    after_delete.png
    
    # Verify the fund is no longer present
    Page Should Not Contain    ${FUND_NAME}
    
    [Teardown]    Close Browser

*** Keywords ***
Click Success OK Button Using JavaScript
    [Documentation]    Click the OK button in the success dialog using JavaScript
    Execute JavaScript    
    ...    var okButton = document.querySelector('div.swal-overlay--show-modal button');
    ...    if(okButton) {
    ...        okButton.click();
    ...        return true;
    ...    }
    ...    return false;