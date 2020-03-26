<form action="{$action}" id="msp-applepay-form" method="POST" class="additional-information">
  <input type="hidden" name="gateway" value="applepay"/>
  <script>
    var applePayPaymentOptionsBlock = document.getElementById('msp-applepay-form').parentElement;
    var paymentMethodId = applePayPaymentOptionsBlock.getAttribute('id').match(/\d/g)[0];
    var PaymentMethodBlock = document.getElementById('payment-option-' + paymentMethodId + '-container');
    PaymentMethodBlock.style.display = 'none';

    try {
      if (window.ApplePaySession && window.ApplePaySession.canMakePayments()) {
        PaymentMethodBlock.style.display = 'block';
      }
    } catch (error) {
      console.warn('MultiSafepay error when trying to initialize Apple Pay:', error);
    }
  </script>
</form>
