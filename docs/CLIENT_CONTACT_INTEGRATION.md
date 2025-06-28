# Client Contact Integration

## Overview

This document details the recent integration of client and contact information into a unified form system, implemented in June 2025 to streamline the client creation process and resolve validation issues.

## Problem Statement

Previously, SolidInvoice had separate Client and Contact entities with a validation constraint requiring at least one Contact per Client. When contact information was merged into the Client form, the validation would fail with "You need to add at least one contact to this client" because no Contact entity was being created.

## Solution Implementation

### 1. Database Schema Changes

**Migration**: `Version20250628165800.php`
- Added `phone` VARCHAR(50) column to `clients` table
- Maintains backward compatibility with existing data

**Client Entity Updates** (`src/ClientBundle/Entity/Client.php`)
- Added `phone` property with proper annotations
- Includes API Platform serialization groups
- Length validation (max 50 characters)
- Nullable field for optional phone numbers

```php
#[ApiProperty(iris: ['https://schema.org/telephone'])]
#[ORM\Column(name: 'phone', type: Types::STRING, length: 50, nullable: true)]
#[Assert\Length(max: 50)]
#[Serialize\Groups(['client_api:read', 'client_api:write'])]
private ?string $phone = null;
```

### 2. Form Integration

**ClientType Form** (`src/ClientBundle/Form/Type/ClientType.php`)
- Added phone field using Symfony's `TelType`
- Positioned between lastName and email fields
- Optional field with "Phone" label

**Template Updates** (`src/ClientBundle/Resources/views/Components/ClientForm.html.twig`)
- Renamed section from "Contact Info" to "Client Contact"
- Added phone field in correct position
- Maintains existing form structure and styling

### 3. Automatic Contact Creation

**ClientForm Component** (`src/ClientBundle/Twig/Components/ClientForm.php`)

Enhanced the `save()` method to automatically create Contact entities:

```php
// Automatically create a Contact from the client data if no contacts exist
if ($client->getContacts()->isEmpty()) {
    $contact = new Contact();
    $contact->setFirstName($client->getFirstName());
    $contact->setLastName($client->getLastName());
    $contact->setEmail($client->getEmail());
    $contact->setClient($client);
    $contact->setCompany($client->getCompany());
    
    // Add phone number as additional contact detail if provided
    if ($client->getPhone()) {
        $phoneType = $manager->getRepository(ContactType::class)
            ->findOneBy(['name' => 'phone', 'company' => $client->getCompany()]);
        
        if ($phoneType) {
            $phoneDetail = new AdditionalContactDetail();
            $phoneDetail->setValue($client->getPhone());
            $phoneDetail->setType($phoneType);
            $phoneDetail->setContact($contact);
            $phoneDetail->setCompany($client->getCompany());
            
            $contact->addAdditionalContactDetail($phoneDetail);
            $manager->persist($phoneDetail);
        }
    }
    
    $client->addContact($contact);
    $manager->persist($contact);
}
```

## Key Benefits

### 1. Streamlined User Experience
- Single "Client Contact" form section instead of separate client/contact forms
- Reduced form complexity while maintaining all functionality
- Phone number field positioned logically above email

### 2. Data Integrity
- Automatic Contact entity creation satisfies validation constraints
- Maintains relationship consistency between Client and Contact entities
- Preserves existing ContactType system for additional contact details

### 3. Backward Compatibility
- Existing clients and contacts remain unaffected
- Legacy ContactType system continues to work
- Phone numbers stored in both Client.phone and AdditionalContactDetail systems

### 4. Developer Experience
- Clear separation of concerns with automatic entity management
- Follows existing patterns in the codebase
- Proper error handling and validation

## Technical Details

### Entity Relationships
- `Client` 1:N `Contact` (unchanged)
- `Contact` 1:N `AdditionalContactDetail` (unchanged)
- `ContactType` 1:N `AdditionalContactDetail` (unchanged)
- New: `Client.phone` field for direct access

### Validation
- Client form validation remains unchanged
- Contact creation happens after client validation passes
- Phone field validation through Assert\Length annotation

### API Integration
- Phone field included in API serialization groups
- Maintains API Platform compatibility
- Schema.org telephone property annotation

## Files Modified

| File Path | Changes |
|-----------|---------|
| `src/ClientBundle/Entity/Client.php` | Added phone property with annotations and getter/setter |
| `src/ClientBundle/Form/Type/ClientType.php` | Added phone field with TelType and "Phone" label |
| `src/ClientBundle/Resources/views/Components/ClientForm.html.twig` | Updated section title and added phone field |
| `src/ClientBundle/Twig/Components/ClientForm.php` | Enhanced save method with automatic contact creation |
| `migrations/Version20250628165800.php` | Database migration for phone column |

## Testing Considerations

### Manual Testing
1. Create new client with phone number
2. Verify "Client Contact" section displays correctly
3. Confirm phone field appears above email field
4. Test client creation completes without validation errors
5. Verify Contact entity is automatically created

### Database Verification
- Check `clients` table has `phone` column
- Verify `contacts` table gets new entries when clients are created
- Confirm `contact_details` table receives phone number entries when ContactType exists

## Future Considerations

### Potential Enhancements
- Add phone number validation (regex patterns for different countries)
- Support multiple phone numbers per client
- Integration with communication features (SMS notifications)
- Import/export functionality for phone numbers

### Migration Path
- Existing installations will automatically get the phone column through migrations
- No data migration needed as field is nullable
- Existing workflows remain unchanged

## Troubleshooting

### Common Issues
1. **"client.form.phone" displays instead of "Phone"**
   - Fixed in ClientType.php by using direct string label instead of translation key

2. **Contact not created automatically**
   - Verify ContactType with name "phone" exists for the company
   - Check that ClientForm.save() method is being called

3. **Phone field not appearing**
   - Clear Symfony cache: `php bin/console cache:clear`
   - Verify form template has been updated

### Debug Commands
```bash
# Check database schema
php bin/console doctrine:schema:validate

# Verify migrations
php bin/console doctrine:migrations:status

# Check for phone ContactType
php bin/console doctrine:query:sql "SELECT * FROM contact_types WHERE name = 'phone'"
```

## Related Documentation
- [Database Management](DATABASE.md)
- [Architecture Overview](ARCHITECTURE.md)
- [Frontend Development](FRONTEND_DEVELOPMENT.md)