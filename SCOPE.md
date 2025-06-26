# Project Scope: Tree Felling Business Management System

## Project Overview

**Objective**: Transform SolidInvoice into a specialized tree felling business management system to replace Tradify HQ, focusing on simplicity, reliability, and core workflow optimization.

**Timeline**: 8-14 weeks total development
**Budget Context**: Current cost $500/year for Tradify HQ subscription

## Core Features (Must-Have)

### 1. Job Management System 🎯 **PRIMARY FOCUS**

#### Current Tradify Status Workflow (To Replicate & Improve):
```
🔴 Unassigned (Red) → Quote needed
🟠 On Hold (Orange) → Quote sent, awaiting response  
🟢 In Progress (Green) → Job confirmed, ready to execute
🩷 To Invoice (Pink) → Job complete, needs invoicing
🆕 Invoice Sent → NEW status for unpaid invoices
⚫ Cancelled (Grey) → Keep as-is
🔵 Complete (Blue) → Keep as-is
```

#### Technical Requirements:
- **Job Entity**: Date, time, customer, address, description, status
- **Status Workflow**: Simple state machine with color coding
- **Integration**: Direct quote/invoice generation from job
- **Auto-population**: Customer details, business templates
- **Map Markers**: Status colors reflected on map

### 2. Customer Management System 👥 **SIMPLIFY EXISTING**

#### Core Functionality:
- **Existing Customer Lookup**: Auto-populate phone, email, name
- **Quick Customer Creation**: From enquiries or jobs
- **Address Management**: Street address + coordinates for mapping
- **Contact History**: Link to jobs, quotes, invoices

#### Technical Implementation:
- Leverage existing SolidInvoice ClientBundle
- Extend Address entity with latitude/longitude
- Streamline UI for quick access

### 3. Enquiries System 📞 **NEW FEATURE**

#### Business Purpose:
- **Phone Call Capture**: Quick note-taking during customer calls
- **Lead Conversion**: Transform enquiry → job → customer
- **Temporary Storage**: Hold potential leads before commitment

#### Technical Requirements:
- **Enquiry Entity**: Name, phone, email, notes, date, status
- **Quick Form**: Minimal fields, fast entry
- **Conversion Actions**: One-click job creation + customer save
- **Simple Interface**: Focus on speed over features

### 4. Google Maps Integration 🗺️ **CRITICAL FEATURE**

#### Business Value:
- **Visual Planning**: See all jobs by location and status
- **Route Optimization**: Plan daily work by geographic proximity
- **Status Overview**: Color-coded markers for quick assessment

#### Technical Requirements:
- **Google Maps API**: Display jobs as colored markers
- **Geocoding**: Convert addresses to coordinates automatically
- **Clustering**: Group nearby jobs for better visualization
- **Interactive**: Click markers for job details
- **Real-time Updates**: Status changes reflect immediately

### 5. Email System Overhaul 📧 **MAJOR PAIN POINT**

#### Current Tradify Issues:
- Emails send from Tradify servers (not business email)
- System expires/fails requiring manual sending
- Unreliable delivery

#### Solution Requirements:
- **Business Email**: Send from actual business email address
- **SMTP Configuration**: Use business email provider
- **Template System**: Pre-configured email templates
- **Reliability**: Robust error handling and retry logic
- **Tracking**: Delivery confirmation (nice-to-have)

### 6. Payment Template System 💰 **MAJOR PAIN POINT**

#### Current Tradify Issues:
- Payment details not saved in templates
- Must re-enter bank details for every invoice

#### Solution Requirements:
- **Persistent Templates**: Save payment details once, use everywhere
- **Auto-population**: Bank details, ABN, business info
- **Template Management**: Multiple templates for different services
- **Integration**: Seamless inclusion in quotes/invoices

## Technical Architecture

### Backend Extensions
```
src/JobBundle/           # New - Job management system
├── Entity/Job.php
├── Entity/JobStatus.php
├── Entity/Enquiry.php
├── Repository/
├── Action/
└── Form/

src/ClientBundle/        # Extend existing
├── Entity/Address.php   # Add latitude/longitude
└── ...existing files

src/InvoiceBundle/       # Enhance existing  
├── Template/            # Add template system
└── ...existing files

src/MailerBundle/        # Enhance existing
├── BusinessMailer.php   # Custom business email sender
└── ...existing files
```

### Frontend Components
```
assets/controllers/
├── job-management-controller.ts    # Job status updates
├── enquiry-form-controller.ts      # Quick enquiry capture  
├── google-maps-controller.ts       # Map integration
└── template-manager-controller.ts  # Payment templates

assets/scss/
├── jobs.scss           # Job-specific styling
├── maps.scss           # Map interface styling
└── enquiries.scss      # Enquiry form styling
```

### Database Schema Changes
```sql
-- New Tables
CREATE TABLE jobs (
    id ULID PRIMARY KEY,
    client_id ULID REFERENCES clients(id),
    title VARCHAR(255),
    description TEXT,
    status VARCHAR(50),
    scheduled_date DATE,
    created_at TIMESTAMP,
    updated_at TIMESTAMP
);

CREATE TABLE enquiries (
    id ULID PRIMARY KEY,
    name VARCHAR(255),
    phone VARCHAR(50),
    email VARCHAR(255),
    notes TEXT,
    status VARCHAR(50),
    created_at TIMESTAMP
);

-- Extend Existing
ALTER TABLE addresses ADD COLUMN latitude DECIMAL(10, 8);
ALTER TABLE addresses ADD COLUMN longitude DECIMAL(11, 8);
```

## User Interface Design Principles

### 1. Simplicity First
- **Minimal Clicks**: Common actions in 1-2 clicks
- **Clean Layout**: Focus on essential information
- **Mobile Friendly**: Responsive design for field use

### 2. Workflow Optimization  
- **Job-Centric**: Everything revolves around jobs
- **Quick Actions**: Status updates, quote generation
- **Auto-population**: Reduce manual data entry

### 3. Visual Clarity
- **Color Coding**: Consistent status colors throughout
- **Clear Typography**: Easy reading on mobile devices
- **Logical Navigation**: Intuitive menu structure

## Success Criteria

### Functional Requirements
- ✅ **Job Status Management**: 7 status workflow with color coding
- ✅ **Customer Auto-population**: Existing customer details fill automatically
- ✅ **Enquiry Conversion**: Phone call → enquiry → job → customer
- ✅ **Map Visualization**: All jobs displayed with status colors
- ✅ **Email Reliability**: 100% delivery from business email
- ✅ **Template Persistence**: Payment details saved and reused

### Performance Requirements
- **Page Load**: < 2 seconds for job listing
- **Map Loading**: < 3 seconds for full job display
- **Form Submission**: < 1 second for status updates
- **Email Sending**: < 5 seconds for quote/invoice delivery

### Business Requirements
- **Cost Savings**: Eliminate $500/year Tradify subscription
- **Time Savings**: 50% reduction in quote/invoice creation time
- **Reliability**: Zero failed email deliveries
- **Usability**: Intuitive enough for immediate adoption

## Out of Scope (Phase 1)

### Features NOT Included:
- ❌ **Advanced Scheduling**: Calendar integration, recurring jobs
- ❌ **Inventory Management**: Equipment, materials tracking  
- ❌ **Time Tracking**: Employee hours, job timing
- ❌ **Advanced Reporting**: Analytics, business intelligence
- ❌ **Mobile App**: Native iOS/Android applications
- ❌ **Multi-user Permissions**: Role-based access control
- ❌ **Integration APIs**: Third-party service connections

### Future Considerations:
- **Phase 2**: Advanced scheduling and calendar integration
- **Phase 3**: Mobile app development
- **Phase 4**: Multi-business platform (serve other tree services)

## Risk Assessment

### Technical Risks
- **Google Maps API**: Costs and rate limits
- **Email Delivery**: SMTP configuration complexity
- **Data Migration**: Moving from Tradify to new system

### Business Risks  
- **Adoption**: Learning curve from Tradify
- **Reliability**: System must be more reliable than Tradify
- **Feature Gaps**: Missing functionality from current system

### Mitigation Strategies
- **Phased Rollout**: Gradual migration with parallel systems
- **Backup Plans**: Fallback to manual processes if needed
- **User Training**: Documentation and hands-on training
- **Testing**: Extensive testing before full deployment

## Delivery Milestones

### Milestone 1: Core Job Management (Week 4)
- Job entity and status workflow
- Basic job listing and status updates
- Customer integration

### Milestone 2: Enquiries & Email (Week 8)  
- Enquiry system implementation
- Business email configuration
- Template system foundation

### Milestone 3: Maps & Templates (Week 12)
- Google Maps integration
- Payment template system
- Auto-population features

### Milestone 4: Polish & Deploy (Week 14)
- UI/UX refinements
- Performance optimization
- Production deployment

This scope document provides clear boundaries and expectations for transforming SolidInvoice into a focused, reliable tree felling business management system that addresses the specific pain points identified with Tradify HQ.