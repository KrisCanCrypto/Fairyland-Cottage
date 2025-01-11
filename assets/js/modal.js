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
  });

// Reset the form
this.reset();
