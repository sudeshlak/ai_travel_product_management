import { configureStore, combineReducers } from '@reduxjs/toolkit'
import { persistStore, persistReducer, type Storage } from 'redux-persist'
import { getToken } from '@/service/localStorageService'
import authReducer, { resetAuth } from '@/store/slices/authSlice'

/** Vite ESM interop breaks `redux-persist/lib/storage`; use window.localStorage directly. */
const persistStorage: Storage = {
  getItem: (key) => Promise.resolve(localStorage.getItem(key)),
  setItem: (key, value) => {
    localStorage.setItem(key, value)
    return Promise.resolve()
  },
  removeItem: (key) => {
    localStorage.removeItem(key)
    return Promise.resolve()
  },
}

const authPersistConfig = {
  key: 'auth',
  storage: persistStorage,
  whitelist: ['user', 'isAuthenticated'],
}

const rootReducer = combineReducers({
  auth: persistReducer(authPersistConfig, authReducer),
})

export const store = configureStore({
  reducer: rootReducer,
  middleware: (getDefaultMiddleware) =>
    getDefaultMiddleware({ serializableCheck: false }),
})

export const persistor = persistStore(store, null, () => {
  const { auth } = store.getState()
  if (auth.isAuthenticated && !getToken()) {
    store.dispatch(resetAuth())
  }
})

export type RootState = ReturnType<typeof store.getState>
export type AppDispatch = typeof store.dispatch
