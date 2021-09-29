var card = new Card({
    // a selector or DOM element for the form where users will
    // be entering their information
    form: '.checkout.woocommerce-checkout', // *required*
    // a selector or DOM element for the container
    // where you want the card to appear
    container: '.card-wrapper', // *required*

    formSelectors: {
        numberInput: 'input[name="billing_card_number"]', // optional — default input[name="number"]
        expiryInput: 'input[name="billing_card_expiry"]', // optional — default input[name="expiry"]
        cvcInput: 'input[name="billing_card_cvc"]', // optional — default input[name="cvc"]
        nameInput: 'input[name="billing_card_name"]' // optional - defaults input[name="name"]
    },

    // Strings for translation - optional
    messages: {
        validDate: 'valid\ndate', // optional - default 'valid\nthru'
        monthYear: 'mm/yyyy', // optional - default 'month/year'
    },

    // Default placeholders for rendered fields - optional
    placeholders: {
        number: '•••• •••• •••• ••••',
        name: 'Nome completo',
        expiry: '••/••',
        cvc: '•••'
    },

    // if true, will log helpful messages for setting up Card
    debug: true // optional - default false
});