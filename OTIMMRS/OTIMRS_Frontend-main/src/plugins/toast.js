import { useToast } from 'vue-toastification'
import "vue-toastification/dist/index.css"

let toastInstance = null

export default {
  init() {
    toastInstance = useToast()
  },
  success(message) {
    toastInstance?.success(message)
  },
  error(message) {
    toastInstance?.error(message)
  },
  info(message) {
    toastInstance?.info(message)
  },
  warning(message) {
    toastInstance?.warning(message)
  },
  fire(options) {
    if (options.icon === 'success') {
      toastInstance?.success(options.title)
    } else if (options.icon === 'error') {
      toastInstance?.error(options.title) 
    } else if (options.icon === 'info') {
      toastInstance?.info(options.title)
    } else if (options.icon === 'warning') {
      toastInstance?.warning(options.title)
    }
  }
}
