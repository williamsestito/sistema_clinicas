const baseURL = (import.meta.env.VITE_API_BASE_URL || 'http://localhost:8080/api') + '/public/' + (import.meta.env.VITE_TENANT_ID || 1)

async function get<T>(path: string): Promise<T> {
  const res = await fetch(`${baseURL}${path}`, {
    headers: { Accept: 'application/json' },
  })
  if (!res.ok) throw new Error(`API error ${res.status}`)
  return res.json()
}

export interface SiteSection {
  id: number
  slug: string
  title: string | null
  content: string | null
  image_url: string | null
  position: number
  active: boolean
}

export interface SiteSettings {
  site_title: string | null
  tagline: string | null
  about_title: string | null
  about_text: string | null
  contact_phone: string | null
  contact_email: string | null
  address: string | null
  instagram_url: string | null
  facebook_url: string | null
  whatsapp_url: string | null
}

export interface SiteTestimonial {
  id: number
  client_name: string
  rating: number
  comment: string
  photo_url: string | null
  visible: boolean
}

export interface SiteService {
  id: number
  name: string
  description: string | null
  duration_min: number
  price: number
}

export interface SiteProfessional {
  id: number
  user_id: number
  specialty: string
  bio: string | null
  photo_url: string | null
  user: { id: number; name: string }
}

export async function fetchSections(): Promise<SiteSection[]> {
  return get<SiteSection[]>('/sections')
}

export async function fetchSettings(): Promise<SiteSettings | null> {
  try {
    return await get<SiteSettings>('/settings')
  } catch {
    return null
  }
}

export async function fetchTestimonials(): Promise<SiteTestimonial[]> {
  try {
    const data = await get<any>('/testimonials')
    return data.testimonials ?? data
  } catch {
    return []
  }
}

export async function fetchServices(): Promise<SiteService[]> {
  try {
    return await get<SiteService[]>('/services')
  } catch {
    return []
  }
}

export async function fetchProfessionals(): Promise<SiteProfessional[]> {
  try {
    return await get<SiteProfessional[]>('/professionals')
  } catch {
    return []
  }
}
