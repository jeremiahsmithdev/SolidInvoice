import { Controller } from "@hotwired/stimulus"

interface ClientData {
  id: string;
  firstName: string;
  lastName: string;
  email: string;
  phone?: string;
  addresses: string[];
}

interface QuoteData {
  id: string;
  client: string;
  quoteId: string;
  status: string;
  total: number;
  baseTotal: number;
  tax: number;
  lines: QuoteLine[];
  terms?: string;
  notes?: string;
}

interface QuoteLine {
  id: string;
  description: string;
  price: number;
  qty: number;
  total: number;
}

interface AddressData {
  street1: string;
  street2?: string;
  city: string;
  state: string;
  zip: string;
  countryName: string;
}

/**
 * Quote Details Controller
 * 
 * Fetches and displays both quote and client information when a quote is selected
 * in the job creation form. The client field is removed - selection is quote-driven.
 */
export default class extends Controller {
  static targets = ["details", "contact", "address", "quoteId", "total", "description"]
  static values = { 
    apiToken: String,
    apiUrl: String 
  }

  declare readonly detailsTarget: HTMLElement
  declare readonly contactTarget: HTMLElement
  declare readonly addressTarget: HTMLElement
  declare readonly quoteIdTarget: HTMLElement
  declare readonly totalTarget: HTMLElement
  declare readonly descriptionTarget: HTMLElement
  declare readonly apiTokenValue: string
  declare readonly apiUrlValue: string

  private quoteField: HTMLInputElement | HTMLSelectElement | null = null

  connect() {
    console.log('Quote details controller connected')
    this.findQuoteField()
    this.setupEventListeners()
  }

  disconnect() {
    this.removeEventListeners()
  }

  /**
   * Find the quote field element
   */
  private findQuoteField(): void {
    // Try multiple selectors for different field types
    const selectors = [
      'select[name="job[quote]"]',
      'input[name="job[quote]"]',
      '.quote-select'
    ]

    for (const selector of selectors) {
      const field = this.element.querySelector(selector) as HTMLInputElement | HTMLSelectElement
      if (field) {
        this.quoteField = field
        console.log('Found quote field:', field)
        break
      }
    }

    if (!this.quoteField) {
      console.warn('Quote field not found')
    }
  }


  /**
   * Set up event listeners for quote field changes
   */
  private setupEventListeners(): void {
    if (this.quoteField) {
      this.quoteField.addEventListener('change', this.handleQuoteChange.bind(this))
      this.quoteField.addEventListener('input', this.handleQuoteInput.bind(this))
    }
  }

  /**
   * Remove event listeners
   */
  private removeEventListeners(): void {
    if (this.quoteField) {
      this.quoteField.removeEventListener('change', this.handleQuoteChange.bind(this))
      this.quoteField.removeEventListener('input', this.handleQuoteInput.bind(this))
    }
  }

  /**
   * Handle quote change events
   */
  private handleQuoteChange(event: Event): void {
    const target = event.target as HTMLInputElement | HTMLSelectElement
    console.log('Quote change detected:', target.value)
    
    if (!target.value || target.value === '') {
      this.hideDetails()
    } else {
      this.loadQuoteDetails(target.value)
    }
  }

  /**
   * Handle quote input events
   */
  private handleQuoteInput(event: Event): void {
    const target = event.target as HTMLInputElement
    console.log('Quote input detected:', target.value)
    
    // Delay to allow autocomplete to complete
    setTimeout(() => {
      if (this.isValidId(target.value)) {
        this.loadQuoteDetails(target.value)
      } else if (!target.value || target.value === '') {
        this.hideDetails()
      }
    }, 300)
  }

  /**
   * Check if the value looks like a valid ID (ULID)
   */
  private isValidId(value: string): boolean {
    return !!(value && value.length > 10) // Basic ULID validation
  }

  /**
   * Load quote details and associated client information
   */
  private async loadQuoteDetails(quoteId: string): Promise<void> {
    if (!this.isValidId(quoteId)) {
      this.hideDetails()
      return
    }

    try {
      console.log('Fetching quote details for ID:', quoteId)
      
      const response = await fetch(`${this.apiUrlValue}/api/quotes/${quoteId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-API-TOKEN': this.apiTokenValue
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const quoteData: QuoteData = await response.json()
      console.log('Quote data received:', quoteData)

      if (quoteData && quoteData.id) {
        await this.displayQuoteDetails(quoteData)
        
        if (quoteData.client) {
          const clientId = this.extractIdFromIri(quoteData.client)
          if (clientId) {
            await this.loadAndDisplayClientDetails(clientId)
          }
        }
        
        this.showDetails()
      }
    } catch (error) {
      console.error('Error fetching quote details:', error)
      this.hideDetails()
    }
  }

  /**
   * Display quote details in the UI
   */
  private async displayQuoteDetails(quote: QuoteData): Promise<void> {
    // Display quote ID
    this.quoteIdTarget.innerHTML = quote.quoteId || quote.id

    // Display total amount (format as currency)
    const totalAmount = quote.total || quote.baseTotal || 0
    this.totalTarget.innerHTML = this.formatCurrency(totalAmount)

    // Display description from quote lines
    const description = this.buildQuoteDescription(quote.lines || [])
    this.descriptionTarget.innerHTML = description
  }

  /**
   * Load and display client details
   */
  private async loadAndDisplayClientDetails(clientId: string): Promise<void> {
    try {
      console.log('Fetching client details for ID:', clientId)
      
      const response = await fetch(`${this.apiUrlValue}/api/clients/${clientId}`, {
        method: 'GET',
        headers: {
          'Accept': 'application/json',
          'Content-Type': 'application/json',
          'X-API-TOKEN': this.apiTokenValue
        }
      })

      if (!response.ok) {
        throw new Error(`HTTP ${response.status}`)
      }

      const clientData: ClientData = await response.json()
      console.log('Client data received:', clientData)

      if (clientData && clientData.id) {
        await this.displayClientDetails(clientData)
      }
    } catch (error) {
      console.error('Error fetching client details:', error)
      this.contactTarget.innerHTML = 'Client information unavailable'
      this.addressTarget.innerHTML = 'Address information unavailable'
    }
  }

  /**
   * Display client details in the UI
   */
  private async displayClientDetails(client: ClientData): Promise<void> {
    const contactInfo: string[] = []
    const addressInfo: string[] = []

    // Display basic client info
    if (client.firstName || client.lastName) {
      const name = [client.firstName, client.lastName].filter(n => n).join(' ')
      contactInfo.push(`<strong>${name}</strong>`)
    }

    if (client.email) {
      contactInfo.push(`Email: ${client.email}`)
    }

    if (client.phone) {
      contactInfo.push(`Phone: ${client.phone}`)
    }

    // Fetch addresses if they are IRIs
    if (client.addresses && client.addresses.length > 0) {
      for (const addressIri of client.addresses) {
        if (typeof addressIri === 'string' && addressIri.startsWith('/api/')) {
          try {
            const addressData = await this.fetchAddress(addressIri)
            if (addressData) {
              const addressParts: string[] = []
              
              if (addressData.street1) addressParts.push(addressData.street1)
              if (addressData.street2) addressParts.push(addressData.street2)
              if (addressData.city) addressParts.push(addressData.city)
              if (addressData.state) addressParts.push(addressData.state)
              if (addressData.zip) addressParts.push(addressData.zip)
              if (addressData.countryName) addressParts.push(addressData.countryName)
              
              if (addressParts.length > 0) {
                addressInfo.push(addressParts.join(', '))
              }
            }
          } catch (error) {
            console.error('Error fetching address:', error)
          }
        }
      }
    }

    // Update UI
    this.contactTarget.innerHTML = contactInfo.length > 0 
      ? contactInfo.join('<br>') 
      : 'No contact information available'
    
    this.addressTarget.innerHTML = addressInfo.length > 0 
      ? addressInfo.join('<br>') 
      : 'No address available'
  }


  /**
   * Extract ID from API Platform IRI
   */
  private extractIdFromIri(iri: string): string | null {
    // Handle both full IRI ("/api/clients/01ABC123...") and plain ID ("01ABC123...")
    if (iri.startsWith('/api/')) {
      const parts = iri.split('/')
      return parts[parts.length - 1] || null
    }
    return this.isValidId(iri) ? iri : null
  }

  /**
   * Fetch address data from API
   */
  private async fetchAddress(addressIri: string): Promise<AddressData | null> {
    const response = await fetch(`${this.apiUrlValue}${addressIri}`, {
      method: 'GET',
      headers: {
        'Accept': 'application/json',
        'Content-Type': 'application/json',
        'X-API-TOKEN': this.apiTokenValue
      }
    })

    if (response.ok) {
      return response.json()
    }

    return null
  }

  /**
   * Format currency amount
   */
  private formatCurrency(amount: number): string {
    return new Intl.NumberFormat('en-US', {
      style: 'currency',
      currency: 'USD'
    }).format(amount / 100) // Assuming amounts are stored in cents
  }


  /**
   * Build description from quote lines
   */
  private buildQuoteDescription(lines: QuoteLine[]): string {
    if (!lines || lines.length === 0) {
      return 'No line items available'
    }

    const descriptions = lines.map(line => {
      const qty = line.qty > 1 ? `${line.qty}x ` : ''
      return `${qty}${line.description}`
    })

    if (descriptions.length <= 3) {
      return descriptions.join('<br>')
    } else {
      const shown = descriptions.slice(0, 3).join('<br>')
      const remaining = descriptions.length - 3
      return `${shown}<br><em>... and ${remaining} more item${remaining > 1 ? 's' : ''}</em>`
    }
  }

  /**
   * Show the quote details panel
   */
  private showDetails(): void {
    (this.detailsTarget.style as any).display = 'block'
  }

  /**
   * Hide the quote details panel
   */
  private hideDetails(): void {
    (this.detailsTarget.style as any).display = 'none'
  }
}