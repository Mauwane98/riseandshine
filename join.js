document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('joinForm');
    const ageInput = document.getElementById('age');
    const feeMsg = document.getElementById('feeMessage');
    const fullNameInput = document.getElementById('fullName');
    const signatureInput = document.getElementById('digitalSignature');
    const joiningFeeCheckbox = document.getElementById('joiningFee');
  
    // Dynamically show joining fee
    ageInput.addEventListener('input', () => {
      const age = parseInt(ageInput.value, 10);
  
      if (!isNaN(age) && age >= 5 && age <= 16) {
        const fee = age < 10 ? 100 : 150;
        feeMsg.textContent = `Your joining fee is R${fee}`;
      } else {
        feeMsg.textContent = '';
      }
    });
  
    form.addEventListener('submit', (event) => {
      const fullName = fullNameInput.value.trim();
      const digitalSignature = signatureInput.value.trim();
      const age = parseInt(ageInput.value, 10);
  
      // Age validation
      if (isNaN(age) || age < 5 || age > 16) {
        alert('Membership is only available for children aged 5 to 16.');
        event.preventDefault();
        ageInput.focus();
        return false;
      }
  
      // Joining fee agreement check
      if (!joiningFeeCheckbox.checked) {
        alert('You must agree to pay the applicable joining fee before submitting.');
        event.preventDefault();
        return false;
      }
  
      // Signature match validation
      if (digitalSignature !== fullName) {
        alert('Digital Signature must exactly match your Full Name.');
        event.preventDefault();
        signatureInput.focus();
        return false;
      }
  
      // Final confirmation
      const fee = age < 10 ? 100 : 150;
      const confirmMsg = `You are ${age} years old.\n\nThe joining fee is R${fee}.\n\nPlease confirm you have paid or will pay this amount via EFT:\n\nAccount No: 123456789\nBank: [Your Bank Name]\nReference: Your Full Name\n\nDo you want to proceed?`;
  
      if (!confirm(confirmMsg)) {
        event.preventDefault();
        return false;
      }
    });
  });
  