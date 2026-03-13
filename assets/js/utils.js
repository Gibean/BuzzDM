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

  /**
   * Valida si un valor es un número telefónico venezolano válido.
   * Acepta: 04141234567, 02121234567, +58 414 123 4567, 58 414 123 4567
   * Móvil: 0414, 0424, 0412, 0416, 0422, 0426, 0415 | Fijo: 02xx (Caracas 0212, etc.)
   * @param {string} value - Valor a validar
   * @returns {{ valid: boolean, normalized?: string, message?: string }}
   */
  window.isValidVenezuelaPhone = function (value) {
    if (!value || typeof value !== "string") return { valid: true }
    const digits = value.replace(/\D/g, "")
    if (digits.length === 0) return { valid: true }
    let normalized = digits
    if (digits.length === 11 && digits.startsWith("0")) normalized = digits.slice(1)
    else if (digits.length === 12 && digits.startsWith("58")) normalized = digits.slice(2)
    else if (digits.length === 13 && digits.startsWith("0058")) normalized = digits.slice(4)
    if (normalized.length !== 10) {
      return { valid: false, message: "El teléfono debe tener 10 dígitos (ej: 04141234567 o 02121234567)" }
    }
    const mobileRe = /^(412|414|415|416|417|418|422|424|426)\d{7}$/
    const landlineRe = /^2[1-9]\d{8}$/
    if (!mobileRe.test(normalized) && !landlineRe.test(normalized)) {
      return { valid: false, message: "Formato inválido. Móvil: 0414/0424/0412... | Fijo: 0212/0241..." }
    }
    return { valid: true, normalized: "+58" + normalized }
  }

  /**
   * Inicializa validación y formato para inputs de teléfono venezolano.
   * Permite solo dígitos, +, espacios, guiones. Valida al salir del campo (blur).
   * @param {HTMLElement|HTMLElement[]} elements - Input(s) de teléfono
   * @param {number} [maxLength=18] - Límite de caracteres (ej: +58 414 123 4567 = 15)
   */
  window.initVenezuelaPhoneValidation = function (elements, maxLength = 18) {
    toArray(elements).forEach((el) => {
      if (!el) return
      el.setAttribute("maxlength", maxLength)
      el.addEventListener("keypress", (e) => {
        const c = e.key
        if (!/[\d+\s\-()]/.test(c) && !["Backspace", "Delete", "Tab", "ArrowLeft", "ArrowRight"].includes(c)) e.preventDefault()
        else if (el.value.length >= maxLength && !["Backspace", "Delete"].includes(c)) e.preventDefault()
      })
      el.addEventListener("paste", (e) => {
        e.preventDefault()
        const pasted = (e.clipboardData?.getData("text") || "").replace(/[^\d+\s\-()]/g, "")
        const start = el.selectionStart
        const end = el.selectionEnd
        const current = el.value
        const newVal = (current.substring(0, start) + pasted + current.substring(end)).replace(/[^\d+\s\-()]/g, "").slice(0, maxLength)
        el.value = newVal
        const pos = Math.min(start + pasted.length, newVal.length)
        el.setSelectionRange(pos, pos)
      })
      el.addEventListener("input", () => {
        let v = el.value.replace(/[^\d+\s\-()]/g, "")
        if (v.length > maxLength) v = v.slice(0, maxLength)
        if (v !== el.value) el.value = v
      })
      el.addEventListener("blur", () => {
        const v = el.value.trim()
        if (!v) return
        const result = window.isValidVenezuelaPhone(v)
        if (!result.valid) {
          el.classList.add("is-invalid")
          el.setCustomValidity(result.message)
        } else {
          el.classList.remove("is-invalid")
          el.setCustomValidity("")
        }
      })
      el.addEventListener("focus", () => {
        el.classList.remove("is-invalid")
        el.setCustomValidity("")
      })
    })
  }
})()
