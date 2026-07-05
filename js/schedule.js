function selectOffering(el) {
  document.querySelectorAll('.offering-option').forEach(function(btn) {
    btn.classList.remove('active');
  });
  el.classList.add('active');
  var desc = document.getElementById('offeringDesc');
  if (el.dataset.value === 'studio') {
    desc.textContent = 'I want to use CodeBuddy Studio to plan, build, and launch my own software.';
  } else {
    desc.textContent = 'I want a professional CodeBuddy engineer to build software for my business.';
  }
}

function handleSubmit(e) {
  e.preventDefault();
  var offering = document.querySelector('.offering-option.active').dataset.value;
  var name = document.getElementById('name').value;
  var email = document.getElementById('email').value;
  var company = document.getElementById('company').value;
  var phone = document.getElementById('phone').value;
  var date = document.getElementById('preferred_date').value;
  var time = document.getElementById('preferred_time').value;
  var message = document.getElementById('message').value;

  var subject = encodeURIComponent('Schedule a Meeting — ' + (offering === 'studio' ? 'CodeBuddy Studio' : 'CodeBuddy Solutions'));
  var body = encodeURIComponent(
    'Hi CodeBuddy team,\n\n' +
    "I'd like to schedule a call to discuss " + (offering === 'studio' ? 'building with CodeBuddy Studio' : 'hiring a CodeBuddy engineer') + '.\n\n' +
    'Name: ' + name + '\n' +
    'Email: ' + email + '\n' +
    'Company: ' + (company || 'N/A') + '\n' +
    'Phone: ' + (phone || 'N/A') + '\n' +
    'Preferred Date: ' + date + '\n' +
    'Preferred Time: ' + time + '\n\n' +
    'Project Details:\n' + (message || 'N/A') + '\n\n' +
    'Looking forward to connecting!'
  );

  // Try PHP first, fallback to mailto
  var xhr = new XMLHttpRequest();
  xhr.open('POST', 'php/send-schedule.php', true);
  xhr.setRequestHeader('Content-Type', 'application/x-www-form-urlencoded');
  xhr.onreadystatechange = function() {
    if (xhr.readyState === 4) {
      if (xhr.status === 200) {
        alert('Thank you! Your meeting request has been submitted. We will confirm your slot shortly.');
        e.target.reset();
      } else {
        window.location.href = 'mailto:support@codebuddy.com?subject=' + subject + '&body=' + body;
      }
    }
  };
  xhr.send(
    'offering=' + encodeURIComponent(offering) +
    '&name=' + encodeURIComponent(name) +
    '&email=' + encodeURIComponent(email) +
    '&company=' + encodeURIComponent(company) +
    '&phone=' + encodeURIComponent(phone) +
    '&date=' + encodeURIComponent(date) +
    '&time=' + encodeURIComponent(time) +
    '&message=' + encodeURIComponent(message)
  );
}

document.addEventListener('DOMContentLoaded', function() {
  var dateInput = document.getElementById('preferred_date');
  if (dateInput) {
    var tomorrow = new Date();
    tomorrow.setDate(tomorrow.getDate() + 1);
    dateInput.value = tomorrow.toISOString().split('T')[0];
  }
});
