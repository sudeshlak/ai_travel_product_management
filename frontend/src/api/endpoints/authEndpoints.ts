import { client } from '@/api/client'
import { mapAxiosError } from '@/api/mapAxiosError'
import type { LoginResponse, UserResponse } from '@/api/responses/authResponse'

export type LoginCredentials = {
  email: string
  password: string
}

export async function login(credentials: LoginCredentials): Promise<LoginResponse> {
  try {
    const { data } = await client.post<LoginResponse>('/v1/auth/login', credentials)
    return data
  } catch (error) {
    mapAxiosError(error)
  }
}

export async function logout(): Promise<void> {
  try {
    await client.post('/v1/auth/logout')
  } catch (error) {
    mapAxiosError(error)
  }
}

export async function me(): Promise<UserResponse> {
  try {
    const { data } = await client.get<{ data: UserResponse }>('/v1/auth/me')
    return data.data
  } catch (error) {
    mapAxiosError(error)
  }
}
