// filepath: /Users/terryloughran/Desktop/Fairyland-Cottage/assets/js/modal.js
document
  .getElementById('contact-form')
  .addEventListener('submit', function (event) {
    event.preventDefault(); // Prevent the default form submission

    // Show the modal
    var modal = document.getElementById('emailSentModal');
    modal.style.display = 'block';

    // Close the modal when the user clicks on the close button
    var closeButton = document.getElementsByClassName('close-button')[0];
    closeButton.onclick = function () {
      modal.style.display = 'none';
    };

    // Close the modal when the user clicks anywhere outside of the modal
    window.onclick = function (event) {
      if (event.target == modal) {
        modal.style.display = 'none';
      }
    };

    // Send form data using EmailJS
    emailjs
      .sendForm('service_j5oer3j', 'template_q6h7j1h', this) // Replace 'YOUR_TEMPLATE_ID' with your actual template ID
      .then(
        function (response) {
          console.log('SUCCESS!', response.status, response.text);
          // Reset the form
          event.target.reset();
        },
        function (error) {
          console.log('FAILED...', error);
        }
      );
    // Reset the form
    this.reset();
  });
