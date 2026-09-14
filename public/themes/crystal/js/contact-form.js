/* ---------------------------------------------
 Contact form
 --------------------------------------------- */
 function handleFormSubmit(e) {
    e.preventDefault(); // Prevent the default form submission behavior

    // Create a FormData object from the form
    var formData = new FormData(e.target);

    // Convert FormData to JSON object
    var formJSON = {};
    formData.forEach(function(value, key){
        formJSON[key] = value;
    });

    $.ajax({
        url: "https://api.web3forms.com/submit",
        type: "POST",
        contentType: "application/json",
        dataType: "json",
        data: JSON.stringify(formJSON),
        success: function(response) {
            // Get the success and error message elements by their IDs
            var successMessage = $("#success-message");
            var errorMessage = $("#error-message");

            // If the status is 200, show success message and reset the form
            if (response.status === 200) {
                successMessage.text("Thank you for your message. We will reply to you shortly!");
                successMessage.removeClass("d-none"); // Show success message
                errorMessage.addClass("d-none"); // Hide error message
                $("#service-form")[0].reset(); // Reset the form
            } else {
                // Log the error response and show an error message
                console.log(response);
                errorMessage.text("Something went wrong! Please try again.");
                errorMessage.removeClass("d-none"); // Show error message
                successMessage.addClass("d-none"); // Hide success message
            }
        },
        error: function(error) {
            // Catch and log any errors during form submission
            console.log(error);

            var errorMessage = $("#error-message");
            errorMessage.text("An error occurred while submitting the form.");
            errorMessage.removeClass("d-none"); // Show error message
        }
    });
}

/* ---------------------------------------------
 Preselect service from the ?service= URL parameter
 (used by the "Book this service" / "Book this style" buttons)
 --------------------------------------------- */
(function () {
    var select = document.getElementById("Service");
    if (!select) return;

    var params = new URLSearchParams(window.location.search);
    var service = params.get("service");
    if (!service) return;

    var matched = false;
    for (var i = 0; i < select.options.length; i++) {
        if (select.options[i].value === service) {
            select.value = service;
            matched = true;
            break;
        }
    }

    if (!matched) return;

    // Scroll to the form after the page (and its loader animation) settles.
    // The native #hash jump happens while the theme's page loader still hides
    // the content, so we re-scroll explicitly.
    var scrollToForm = function () {
        setTimeout(function () {
            var form = document.getElementById("contact_form") || select;
            form.scrollIntoView({ behavior: "smooth", block: "start" });
        }, 400);
    };

    if (document.readyState === "complete") {
        scrollToForm();
    } else {
        window.addEventListener("load", scrollToForm);
    }
})();
