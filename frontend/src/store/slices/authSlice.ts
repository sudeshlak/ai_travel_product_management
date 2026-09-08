import { createAsyncThunk, createSlice } from '@reduxjs/toolkit'
import type { LoginCredentials } from '@/api/endpoints/authEndpoints'
import * as authService from '@/service/authService'
import type { User } from '@/types/User'

type AuthState = {
  user: User | null
  isAuthenticated: boolean
  loading: boolean
  error: Error | undefined
}

const initialState: AuthState = {
  user: null,
  isAuthenticated: false,
  loading: false,
  error: undefined,
}

export const login = createAsyncThunk(
  'auth/login',
  async (credentials: LoginCredentials, { rejectWithValue }) => {
    try {
      return await authService.login(credentials)
    } catch (error) {
      return rejectWithValue(error as Error)
    }
  },
)

export const logout = createAsyncThunk('auth/logout', async () => {
  await authService.logout()
})

const authSlice = createSlice({
  name: 'auth',
  initialState,
  reducers: {
    clearAuthError(state) {
      state.error = undefined
    },
    resetAuth(state) {
      state.user = null
      state.isAuthenticated = false
      state.loading = false
      state.error = undefined
    },
  },
  extraReducers: (builder) => {
    builder
      .addCase(login.pending, (state) => {
        state.loading = true
        state.error = undefined
      })
      .addCase(login.fulfilled, (state, action) => {
        state.loading = false
        state.user = action.payload.user
        state.isAuthenticated = true
        state.error = undefined
      })
      .addCase(login.rejected, (state, action) => {
        state.loading = false
        state.error = action.payload as Error
        state.isAuthenticated = false
        state.user = null
      })
      .addCase(logout.fulfilled, (state) => {
        state.user = null
        state.isAuthenticated = false
        state.loading = false
        state.error = undefined
      })
      .addCase(logout.rejected, (state) => {
        state.user = null
        state.isAuthenticated = false
        state.loading = false
        state.error = undefined
      })
  },
})

export const { clearAuthError, resetAuth } = authSlice.actions
export default authSlice.reducer
