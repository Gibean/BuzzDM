/**
 * Utilidad para validar inputs numéricos: bloquea letras y caracteres no válidos.
 * Uso: initNumericInputValidation({ decimal: [elemento1, elemento2], integer: [elemento3] })
 * - decimal: inputs que aceptan números con punto decimal (ej: precio)
 * - integer: inputs que solo aceptan números enteros (ej: stock, cantidad)
 */
(function () {
  function toArray(val) {
    return val ? (Array.isArray(val) ? val : [val]) : []
  }

  function applyDecimalValidation(el) {
    if (!el) return
    el.addEventListener("keypress", (e) => {
      const char = e.key
      const val = el.value
      if (!/[\d.]/.test(char) || (char === "." && val.includes("."))) e.preventDefault()
    })
    el.addEventListener("paste", (e) => {
      e.preventDefault()
      const pasted = (e.clipboardData?.getData("text") || "").replace(/[^\d.]/g, "")
      const start = el.selectionStart
      const end = el.selectionEnd
      const current = el.value
      const raw = (current.substring(0, start) + pasted + current.substring(end)).replace(/[^\d.]/g, "")
      const parts = raw.split(".")
      const singleDecimal = parts.length > 1 ? parts[0] + "." + (parts[1] || "") : raw
      el.value = singleDecimal
      const pos = Math.min(start + pasted.length, singleDecimal.length)
      el.setSelectionRange(pos, pos)
    })
    el.addEventListener("input", () => {
      const v = el.value
      const cleaned = v.replace(/[^\d.]/g, "")
      const fixed = cleaned.includes(".") ? cleaned.replace(/\./g, "x").replace(/x/, ".").replace(/x/g, "") : cleaned
      if (v !== fixed) el.value = fixed
    })
  }

  function applyIntegerValidation(el) {
    if (!el) return
    el.addEventListener("keypress", (e) => {
      if (!/\d/.test(e.key)) e.preventDefault()
    })
    el.addEventListener("paste", (e) => {
      e.preventDefault()
      const sanitized = (e.clipboardData?.getData("text") || "").replace(/\D/g, "")
      const start = el.selectionStart
      const end = el.selectionEnd
      const current = el.value
      el.value = current.substring(0, start) + sanitized + current.substring(end)
      el.setSelectionRange(start + sanitized.length, start + sanitized.length)
    })
    el.addEventListener("input", () => {
      el.value = el.value.replace(/\D/g, "")
    })
  }

  window.initNumericInputValidation = function (options) {
    const { decimal = [], integer = [] } = options || {}
    toArray(decimal).forEach(applyDecimalValidation)
    toArray(integer).forEach(applyIntegerValidation)
  }
})()
