import {Notify} from "quasar";

const DEFAULT_NOTIFY_TIMEOUT = 1000;
export function useCommonHelper() {

  const useGeneratePassword = (timeoutTime = DEFAULT_NOTIFY_TIMEOUT) => {

    const LETTERS_LENGTH = 6;
    const NUMBERS_LENGTH = 3;
    const SYMBOLS_LENGTH = 3;

    const CHARACTERS = [
      "ABCDEFGHIJKLMNOPQRSTUVWXYZabcdefghijklmnopqrstuvwxyz", // letters
      "0123456789", // numbers
      "!@#$%^&*()_+-=", // symbols
    ];

    const PASSWORD = [LETTERS_LENGTH, NUMBERS_LENGTH, SYMBOLS_LENGTH]
      .map(function (len, i) {
        return Array(len)
          .fill(CHARACTERS[i])
          .map(function (x) {
            return x[Math.floor(Math.random() * x.length)];
          })
          .join("");
      })
      .concat()
      .join("")
      .split("")
      .sort(function () {
        // eslint-disable-next-line no-magic-numbers
        return 0.5 - Math.random();
      })
      .join("");

    Notify.create({
      color: "grey-8",
      message: "Password Generated!",
      position: "bottom",
      timeout: timeoutTime,
    });
    return PASSWORD;
  }

  const copyToClipboard = (
    data = "",
    timeoutTime = DEFAULT_NOTIFY_TIMEOUT
  ) => {
    if (!data) return;

    navigator.clipboard.writeText(data);

    Notify.create({
      color: "grey-8",
      message: "Copied!",
      position: "bottom",
      timeout: timeoutTime,
    });
  };

  return {
    useGeneratePassword,
    copyToClipboard
  }
}
