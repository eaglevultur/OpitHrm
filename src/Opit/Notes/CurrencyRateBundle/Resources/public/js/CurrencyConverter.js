// Generated by CoffeeScript 1.7.1
(function() {
  var CurrencyConverter;

  CurrencyConverter = (function() {
    CurrencyConverter.prototype.defaultRate = 1.0;

    function CurrencyConverter(rates) {
      this.rates = rates;
      if (this.rates == null) {
        throw new Error("Error. Rates are missing.");
      }
    }

    CurrencyConverter.prototype.convertCurrency = function(originCode, destinationCode, value) {
      var destinationRate, originRate, result;
      if (originCode.toUpperCase() === destinationCode.toUpperCase()) {
        return value;
      }
      if ('HUF' === destinationCode.toUpperCase()) {
        destinationRate = this.defaultRate;
      } else {
        destinationRate = this.getRateOfCurrency(destinationCode);
      }
      result = parseFloat(value) / destinationRate;
      if ('HUF' !== originCode.toUpperCase()) {
        originRate = this.getRateOfCurrency(originCode);
        result = parseFloat(result) * originRate;
      }
      return result.toFixed(2);
    };

    CurrencyConverter.prototype.getRateOfCurrency = function(currencyCode) {
      currencyCode = currencyCode.toUpperCase();
      if (!this.rates[currencyCode]) {
        throw new Error("Error. Rate could not be found.");
      }
      return this.rates[currencyCode];
    };

    return CurrencyConverter;

  })();

  window.CurrencyConverter = CurrencyConverter;

}).call(this);
