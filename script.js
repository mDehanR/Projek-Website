function scrollToCategories() {
  const section = document.getElementById("categories");
  section.scrollIntoView({ behavior: "smooth" });
}

// Modal functions
function showModal() {
  document.getElementById('authModal').style.display = 'block';
}

function closeModal() {
  document.getElementById('authModal').style.display = 'none';
}

function goToLogin() {
  window.location.href = 'login.html';
}

function goToRegister() {
  window.location.href = 'register.html';
}

// Smooth scrolling for navbar links
document.addEventListener('DOMContentLoaded', function() {
  const navLinks = document.querySelectorAll('.nav-links a');
  navLinks.forEach(link => {
    link.addEventListener('click', function(e) {
      e.preventDefault();
      const targetId = this.getAttribute('href').substring(1);
      const targetSection = document.getElementById(targetId);
      if (targetSection) {
        targetSection.scrollIntoView({ behavior: 'smooth' });
      }
    });
  });
});
