export function useValidationHelper() {

  const emailValidation = (email) => {
    return /^(?=[a-zA-Z0-9@._%+-]{6,254}$)[a-zA-Z0-9._%+-]{1,64}@(?:[a-zA-Z0-9-]{1,63}\.){1,8}[a-zA-Z]{2,63}$/
      .test(email);
  }

  const passwordMustContainNumberValidation = (password) => {
    return /^(?=.*[0-9])/.test(password);
  }

  const passwordLength = (password) => {
    return /(?=.{6,})/.test(password);
  }

  const passwordMustHaveSymbol = (password) => {
    return /^(?=.*[!@#\$%\^&\*])/.test(password);
  }

  return {
    emailValidation,
    passwordLength,
    passwordMustContainNumberValidation,
    passwordMustHaveSymbol
  }
}
