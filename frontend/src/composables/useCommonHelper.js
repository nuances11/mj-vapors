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

  const deepClone = (source) => {
    if (!source && typeof source !== "object")
      throw new Error("error arguments deepClone");

    const targetObj = source.constructor === Array ? [] : {};
    Object.keys(source).forEach((keys) => {
      if (source[keys] && typeof source[keys] === "object")
        targetObj[keys] = deepClone(source[keys]);
      else targetObj[keys] = source[keys];
    });

    return targetObj;
  }

  const defaultPagination = () => {
    return {
      sortBy: "created_at",
      descending: false,
      page: 1,
      rowsPerPage: 10,
      rowsNumber: 10,
    }
  }

  const numberFormat = (value, decimal) => {
    if (typeof decimal === "undefined") {
      decimal = 2;
    }

    if (typeof value !== "undefined" && value !== null) {
      value = parseFloat(value).toFixed(decimal);
      let parts = value.split(".");
      return (
        parts[0].replace(/(\d)(?=(\d{3})+(?!\d))/g, "$1,") +
        (parts[1] ? "." + parts[1] : "")
      );
    }
    return 0.0;
  }

  const titleCase = (s) =>
    s.replace(/^_*(.)|_+(.)/g, (s, c, d) => c ? c.toUpperCase() : ' ' + d.toUpperCase())

  return {
    useGeneratePassword,
    copyToClipboard,
    deepClone,
    defaultPagination,
    numberFormat,
    titleCase
  }
}
