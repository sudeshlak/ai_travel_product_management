export const PRODUCT_AI_PROMPT_MIN_WORDS = 4
export const PRODUCT_AI_PROMPT_MAX_LENGTH = 2000

export function countWords(text: string): number {
  const trimmed = text.trim()
  if (!trimmed) {
    return 0
  }
  return trimmed.split(/\s+/).filter(Boolean).length
}

export function canUseProductAiPrompt(text: string): boolean {
  return (
    countWords(text) >= PRODUCT_AI_PROMPT_MIN_WORDS &&
    text.length <= PRODUCT_AI_PROMPT_MAX_LENGTH
  )
}
