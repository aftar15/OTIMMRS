// node_modules/pinia-plugin-persistedstate/dist/index.mjs
function isObject(v) {
  return typeof v === "object" && v !== null;
}
function normalizeOptions(options, globalOptions) {
  options = isObject(options) ? options : /* @__PURE__ */ Object.create(null);
  return new Proxy(options, {
    get(target, key, receiver) {
      return Reflect.get(target, key, receiver) || Reflect.get(globalOptions, key, receiver);
    }
  });
}
function get(state, path) {
  return path.reduce((obj, p) => {
    return obj == null ? void 0 : obj[p];
  }, state);
}
function set(state, path, val) {
  return path.slice(0, -1).reduce((obj, p) => {
    if (!/^(__proto__)$/.test(p))
      return obj[p] = obj[p] || {};
    else
      return {};
  }, state)[path[path.length - 1]] = val, state;
}
function pick(baseState, paths) {
  return paths.reduce((substate, path) => {
    const pathArray = path.split(".");
    return set(substate, pathArray, get(baseState, pathArray));
  }, {});
}
function createPersistedState(factoryOptions = {}) {
  return function(context) {
    const {
      options: { persist },
      store
    } = context;
    if (!persist)
      return;
    const {
      storage = localStorage,
      beforeRestore = null,
      afterRestore = null,
      serializer = {
        serialize: JSON.stringify,
        deserialize: JSON.parse
      },
      key = store.$id,
      paths = null
    } = normalizeOptions(persist, factoryOptions);
    beforeRestore == null ? void 0 : beforeRestore(context);
    try {
      const fromStorage = storage.getItem(key);
      if (fromStorage)
        store.$patch(serializer.deserialize(fromStorage));
    } catch (_error) {
    }
    afterRestore == null ? void 0 : afterRestore(context);
    store.$subscribe((_mutation, state) => {
      try {
        const toStore = Array.isArray(paths) ? pick(state, paths) : state;
        storage.setItem(key, serializer.serialize(toStore));
      } catch (_error) {
      }
    }, { detached: true });
  };
}
function createNuxtPersistedState(useCookie, factoryOptions) {
  return createPersistedState({
    storage: {
      getItem: (key) => {
        return useCookie(key, {
          encode: encodeURIComponent,
          decode: decodeURIComponent,
          ...factoryOptions == null ? void 0 : factoryOptions.cookieOptions
        }).value;
      },
      setItem: (key, value) => {
        useCookie(key, {
          encode: encodeURIComponent,
          decode: decodeURIComponent,
          ...factoryOptions == null ? void 0 : factoryOptions.cookieOptions
        }).value = value;
      }
    },
    ...factoryOptions
  });
}
var persistedState = createPersistedState();
export {
  createNuxtPersistedState,
  createPersistedState,
  persistedState as default
};
//# sourceMappingURL=pinia-plugin-persistedstate.js.map
