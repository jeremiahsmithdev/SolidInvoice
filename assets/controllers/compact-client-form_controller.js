/* eslint-env browser */
/* global console, setTimeout */

import { Controller } from '@hotwired/stimulus'

export default class extends Controller {
    static targets = ['container']
    static values = { 
        visible: Boolean,
        duration: { type: Number, default: 400 }
    }

    connect() {
        console.log('CompactClientForm Stimulus controller connected')
        
        // Set initial state based on visibility
        this.updateVisibility(false) // Don't animate on initial load
    }

    visibleValueChanged() {
        console.log('Visibility changed to:', this.visibleValue)
        this.updateVisibility(true) // Animate on value changes
    }

    updateVisibility(animate = true) {
        const container = this.containerTarget
        
        if (this.visibleValue) {
            this.slideDown(container, animate)
        } else {
            this.slideUp(container, animate)
        }
    }

    slideDown(element, animate = true) {
        console.log('Sliding down form')
        
        // Remove d-none to make element visible
        element.classList.remove('d-none')
        
        if (!animate) {
            element.style.height = 'auto'
            element.style.opacity = '1'
            return
        }

        // Get the natural height
        element.style.height = 'auto'
        const height = element.offsetHeight
        
        // Start from 0 height
        element.style.height = '0px'
        element.style.opacity = '0'
        element.style.overflow = 'hidden'
        element.style.transition = `height ${this.durationValue}ms ease-out, opacity ${this.durationValue}ms ease-out`
        
        // Force a reflow
        void element.offsetHeight
        
        // Animate to full height
        element.style.height = height + 'px'
        element.style.opacity = '1'
        
        // Clean up after animation
        setTimeout(() => {
            element.style.height = 'auto'
            element.style.overflow = ''
            element.style.transition = ''
        }, this.durationValue)
    }

    slideUp(element, animate = true) {
        console.log('Sliding up form')
        
        if (!animate) {
            element.classList.add('d-none')
            return
        }

        // Get current height
        const height = element.offsetHeight
        
        // Set explicit height and start transition
        element.style.height = height + 'px'
        element.style.overflow = 'hidden'
        element.style.transition = `height ${this.durationValue}ms ease-out, opacity ${this.durationValue}ms ease-out`
        
        // Force a reflow
        void element.offsetHeight
        
        // Animate to 0 height
        element.style.height = '0px'
        element.style.opacity = '0'
        
        // Hide element after animation
        setTimeout(() => {
            element.classList.add('d-none')
            element.style.height = ''
            element.style.opacity = ''
            element.style.overflow = ''
            element.style.transition = ''
        }, this.durationValue)
    }

    toggle() {
        console.log('Toggle called, current visibility:', this.visibleValue)
        this.visibleValue = !this.visibleValue
    }

    // Handle form submission success
    handleSuccess() {
        console.log('Form submitted successfully, hiding form')
        this.visibleValue = false
    }
}