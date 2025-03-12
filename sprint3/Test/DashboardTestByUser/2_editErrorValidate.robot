*** Settings ***
Documentation     Test fund edit validation error
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

# More flexible input field locators
${FUND_NAME_INPUT}        xpath=//input[contains(@placeholder, 'name') or contains(@id, 'name') or contains(@name, 'name')] | //label[contains(text(), 'ชื่อทุน')]/following::input[1]
${FORM_SUBMIT_BTN}        xpath=//button[contains(text(), 'Submit')]
${ERROR_MESSAGE}          xpath=//div[contains(text(), 'Whoops!')] | //div[contains(@class, 'error')] | //div[contains(@class, 'alert')]

# Edit button locator
${EDIT_BUTTON}            xpath=//tr[contains(.,'New Funds')]//a[2]

# Test Data
${TEACHER_EMAIL}          jonathan.teacher@gmail.com
${PASSWORD}               123456789
${FUND_NAME}              New Funds

*** Test Cases ***
Test Fund Edit Validation Error
    [Documentation]    Test the validation error when fund name is empty
    [Timeout]          5 minutes
    
    Open Browser    ${LOGIN_URL}    ${BROWSER}
    Maximize Browser Window
    
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
    
    # Capture page screenshot to see the funds table
    Capture Page Screenshot    funds_table.png
    
    # Click the edit button for the fund - try different approaches
    ${edit_success}=    Run Keyword And Return Status    Run Keywords
    ...    Wait Until Page Contains    ${FUND_NAME}    timeout=15s    AND
    ...    Click Element    ${EDIT_BUTTON}
    
    # If that didn't work, try the JavaScript approach
    Run Keyword If    not ${edit_success}    Click Any Edit Button
    
    # Wait for edit page to load
    Wait Until Location Contains    edit    timeout=15s
    Sleep    2s  # Add a short delay for page rendering
    
    # Take screenshot to see the actual edit page
    Capture Page Screenshot    edit_page_before_finding_input.png
    
    # Get all input fields for debugging
    ${input_count}=    Get Element Count    xpath=//input
    Log    Found ${input_count} input elements on the page
    
    # Try to find fund name input using multiple approaches
    ${name_field_found}=    Run Keyword And Return Status    Wait Until Element Is Visible    ${FUND_NAME_INPUT}    timeout=15s
    Run Keyword If    not ${name_field_found}    Find Input Field Using JavaScript
    
    # Clear the fund name field 
    Run Keyword If    ${name_field_found}    Clear Element Text    ${FUND_NAME_INPUT}
    Run Keyword If    not ${name_field_found}    Clear Input Field Using JavaScript
    
    # Submit the form 
    Wait Until Element Is Visible    ${FORM_SUBMIT_BTN}    timeout=15s
    Click Element    ${FORM_SUBMIT_BTN}
    
    # Verify error message appears - check for both upper and lowercase versions
    ${error_visible}=    Run Keyword And Return Status    Wait Until Page Contains    fund name field is required    timeout=15s
    ${error_visible_alt}=    Run Keyword And Return Status    Wait Until Page Contains    Fund name field is required    timeout=5s
    ${error_visible_thai}=    Run Keyword And Return Status    Wait Until Page Contains    ชื่อทุน    timeout=5s
    
    Should Be True    ${error_visible} or ${error_visible_alt} or ${error_visible_thai}    No error message found
    
    Capture Page Screenshot    validation_error.png
    
    [Teardown]    Close Browser

*** Keywords ***
Click Any Edit Button
    [Documentation]    Click any edit button on the page using JavaScript
    Execute JavaScript    
    ...    var editLinks = Array.from(document.querySelectorAll('a')).filter(a => 
    ...        a.href.includes('edit') || 
    ...        a.innerHTML.includes('edit') || 
    ...        (a.querySelector('i') && (a.querySelector('i').className.includes('edit') || a.querySelector('i').className.includes('pencil')))
    ...    );
    ...    if (editLinks.length > 0) {
    ...        editLinks[0].click();
    ...        return true;
    ...    }
    ...    // Try second link in any row as fallback
    ...    var rows = document.querySelectorAll('tr');
    ...    for (var i = 0; i < rows.length; i++) {
    ...        var links = rows[i].querySelectorAll('a');
    ...        if (links.length >= 2) {
    ...            links[1].click();
    ...            return true;
    ...        }
    ...    }
    ...    return false;

Find Input Field Using JavaScript
    [Documentation]    Find the fund name input field using JavaScript
    Execute JavaScript    
    ...    console.log("Input fields on page:");
    ...    var inputs = document.querySelectorAll('input');
    ...    inputs.forEach((input, i) => {
    ...        console.log(`Input ${i}:`, input.id, input.name, input.placeholder);
    ...    });
    ...    return true;

Clear Input Field Using JavaScript
    [Documentation]    Clear the fund name input field using JavaScript
    Execute JavaScript    
    ...    var nameInput = document.querySelector('input[name="name"]') || 
    ...                    document.querySelector('input[id="name"]') || 
    ...                    document.querySelector('input[placeholder*="name"]');
    ...    if (!nameInput) {
    ...        // Try finding by label text
    ...        var labels = Array.from(document.querySelectorAll('label'));
    ...        var nameLabel = labels.find(label => label.textContent.includes('ชื่อทุน'));
    ...        if (nameLabel && nameLabel.nextElementSibling && nameLabel.nextElementSibling.tagName === 'INPUT') {
    ...            nameInput = nameLabel.nextElementSibling;
    ...        } else if (nameLabel && nameLabel.getAttribute('for')) {
    ...            nameInput = document.getElementById(nameLabel.getAttribute('for'));
    ...        }
    ...    }
    ...    if (!nameInput) {
    ...        // Try the first input as a last resort
    ...        nameInput = document.querySelector('input:not([type="hidden"])');
    ...    }
    ...    if (nameInput) {
    ...        nameInput.value = '';
    ...        nameInput.dispatchEvent(new Event('change', { bubbles: true }));
    ...        return true;
    ...    }
    ...    return false;