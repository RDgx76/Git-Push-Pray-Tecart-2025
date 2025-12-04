/* validator.js
   Lightweight validators used across pages.
*/

const Validator = {
  isRequired(value) {
    return value !== undefined && value !== null && String(value).trim() !== "";
  },
  isNumber(value) {
    return !isNaN(parseFloat(value)) && isFinite(value);
  },
  isPositiveNumber(value) {
    return this.isNumber(value) && Number(value) >= 0;
  },
  isEmail(value) {
    return /^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(value);
  },
};

window.Validator = Validator;
