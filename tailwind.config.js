module.exports = {
  content: [
    "./*.php",
    "./inc/**/*.php",
    "./template-parts/**/*.php",
    "./assets/js/**/*.js",
    "./assets/js/**/*.php",
    "./functions.php"
  ],
  theme: {
    extend: {
      colors: {
        surface: "#232629",
        deep: "#15171a",
        panel: "#2b2e31",
        brandred: "#c42b29",
        orange: { DEFAULT: "#df6a2e", bright: "#ef7b00" },
        gold: { DEFAULT: "#f5b335", soft: "#f8ab3e" },
        slatey: "#94a3b8"
      },
      fontFamily: {
        sans: ["Figtree", "system-ui", "sans-serif"],
        display: ["Montserrat", "Gotham", "sans-serif"]
      },
      maxWidth: { shell: "1440px" }
    }
  },
  plugins: []
};
