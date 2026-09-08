import {
  login as loginEndpoint,
  logout as logoutEndpoint,
} from '@/api/endpoints/authEndpoints'
import type { LoginCredentials } from '@/api/endpoints/authEndpoints'
import { mapLoginResponse } from '@/service/mappers/authMapper'
import { clearToken, setToken } from '@/service/localStorageService'
import type { User } from '@/types/User'

export async function login(credentials: LoginCredentials): Promise<{ user: User }> {
  const response = await loginEndpoint(credentials)
  const { token, user } = mapLoginResponse(response)
  setToken(token)
  return { user }
}

export async function logout(): Promise<void> {
  try {
    await logoutEndpoint()
  } catch {
    // Best-effort: always clear the local token even if the API call fails.
  } finally {
    clearToken()
  }
}
