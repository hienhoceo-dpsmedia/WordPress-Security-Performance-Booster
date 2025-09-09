# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.12] - 2025-09-09

### Fixed
- **Critical Fix**: Language switching functionality now works correctly
- Fixed textdomain loading timing issue that prevented translations from displaying
- Added proper textdomain unloading and reloading when language settings change
- Improved language detection and loading mechanism
- Added real-time language switching without requiring plugin deactivation/reactivation

### Added
- Language change detection system that reloads translations automatically
- Better error handling for missing translation files
- Improved textdomain loading with proper fallback mechanisms

## [1.0.11] - 2025-09-08

### Added
- Complete Vietnamese translation updates for all plugin strings
- Updated POT template file with all current translatable strings
- Improved language files with proper encoding and metadata

### Fixed
- Fixed version mismatch between plugin files and readme.txt
- Updated version numbers to 1.0.11 for consistency across all files
- Resolved potential activation issues caused by version inconsistencies

## [1.0.10] - 2025-09-08

### Fixed
- Fixed version mismatch between plugin files and readme.txt
- Updated version numbers to 1.0.10 for consistency across all files
- Resolved potential activation issues caused by version inconsistencies

## [1.0.9] - 2025-09-08

### Changed
- Updated plugin version to 1.0.9

## [1.0.8] - 2025-09-08

### Added
- Language selector to force plugin UI language (Auto/vi/en_US/de_DE/fr_FR)
- Improved textdomain loading honoring the selected locale
- Docs note: disabling background checks reduces CPU/RAM usage on VPS/shared hosts

## [1.0.7] - 2025-09-08

### Added
- Granular update controls: separate toggles for core, plugin, and theme updates
- Descriptive settings groups with "what it does" and "when to use" guidance

### Changed
- Conditional HTTP and cron blocking based on selected update channels
- Auto-migration from legacy single "disable_updates" to granular flags

## [1.0.6] - 2025-09-08

### Fixed
- Finalize plugin header structure: loader is the sole header; core file contains no plugin headers
- Publish clean, WordPress-ready package asset

### Notes
- This version is functionally identical to 1.0.5 with packaging/header cleanups

## [1.0.5] - 2025-09-08

### Changed
- Major refactor: split plugin into a PHP 5.2–safe loader (main file) and a core file under `includes/`. This prevents parse errors on older PHP and ensures clean activation behavior.

### Fixed
- Converted remaining short array usages in hook callbacks to long array syntax.
- Maintained activation/deactivation/uninstall behavior through loader wrappers.

## [1.0.4] - 2025-09-08

### Fixed
- Add early PHP version guard to avoid parse/runtime issues on older PHP
- Replace Throwable catch with Exception for broad compatibility
- Guard wp_add_inline_script/wp_add_inline_style for older WP installs

## [1.0.3] - 2025-09-08

### Fixed
- Hardened activation path with try/catch and one-time admin notice
- Guard `$wpdb->check_connection()` and make table checks safer
- Replaced short array syntax in admin-bar notice for older PHP compatibility
- Packaging: prepare minimal WordPress-ready ZIP (exclude CI and dev-only files)

## [1.0.2] - 2025-09-08

### Fixed
- Add compatibility fallback for wp_json_encode() on very old WordPress versions

## [1.0.1] - 2025-09-08

### Fixed
- Register activation/deactivation/uninstall hooks at file scope to ensure reliable activation
- Guard get_plugin_data() usage on front-end to prevent potential fatal error when admin bar renders
- Bump plugin version and stable tag to 1.0.1

### Changed
- Minor internal refactors and safety checks (no behavior change)

## [1.0.0] - 2024-09-01

### Added
- **🛡️ Update Management**
  - Complete WordPress core, theme, and plugin update blocking
  - Advanced transient management for update prevention
  - HTTP request filtering for WordPress.org API calls
  - Cron event filtering to prevent automated checks
  - Site health integration to hide update-related tests

- **🚫 Anti-Spam Protection**
  - Complete comment system disabling across all post types
  - Pingback and trackback blocking with header removal
  - XML-RPC endpoint disabling for security enhancement
  - Comment feed removal and admin menu cleanup
  - Admin bar comment menu removal

- **🧹 Interface Cleanup**
  - Comprehensive admin notification hiding system
  - Dashboard widget removal (WordPress news, activity, quick draft)
  - Plugin promotional message blocking
  - Clean, distraction-free admin interface

- **🌍 Multi-Language Support**
  - Vietnamese (Tiếng Việt) complete translation
  - German (Deutsch) language support
  - French (Français) language support
  - English as default language
  - Language switcher in admin interface
  - Proper WordPress localization implementation

- **⚙️ Modern Admin Interface**
  - Professional DPS.MEDIA branded settings page
  - Toggle switches for selective feature control
  - Real-time status indicators for all features
  - Color-coded status badges (Blocked/Active)
  - Mobile-responsive design with CSS Grid
  - Modern flat design with gradient accents

- **🔒 Security Enhancements**
  - Proper nonce verification for all forms
  - Input sanitization and validation
  - Capability checks for all admin functions
  - Secure data handling throughout plugin
  - Error logging for debugging (when WP_DEBUG enabled)

- **📊 Status Monitoring**
  - Real-time feature status verification
  - Admin bar notification with green shield icon
  - Comprehensive status dashboard
  - Feature effectiveness indicators
  - Live status checking for critical functions

- **🛠️ Developer Features**
  - Complete WordPress Coding Standards compliance
  - Comprehensive PHPDoc documentation
  - Proper uninstall.php for clean removal
  - Version compatibility checks (WordPress 4.0+, PHP 7.4+)
  - Debug logging throughout plugin operation
  - Modular architecture for easy maintenance

### Technical Details

#### **WordPress Compatibility**
- Minimum WordPress: 4.0
- Tested up to: 6.8
- Multisite compatible: Yes
- Network activation: No

#### **PHP Requirements**
- Minimum PHP: 7.4
- Tested up to: PHP 8.2
- Memory requirements: Minimal (< 1MB)
- No external dependencies

#### **Performance Optimizations**
- Singleton pattern for efficient memory usage
- Conditional feature loading based on settings
- Minimal database queries and cached results
- Optimized CSS delivery with inline styles
- Reduced HTTP requests through API blocking

#### **Security Implementation**
- ABSPATH checks in all files
- Nonce verification for form submissions
- Capability checks before any operations
- Input sanitization using WordPress functions
- Output escaping for all displayed data
- SQL injection prevention (no direct queries)

#### **Localization Features**
- Complete .pot template file included
- Vietnamese .po/.mo files ready
- Text domain properly implemented
- Translatable strings properly marked
- Context-aware translations
- RTL language support ready

### Code Quality

#### **WordPress Standards**
- ✅ WordPress Coding Standards compliant
- ✅ WordPress Plugin Guidelines followed
- ✅ GPL v2 or later license compliance
- ✅ Proper plugin headers and metadata
- ✅ Security best practices implemented
- ✅ Accessibility guidelines followed

#### **Documentation**
- ✅ Comprehensive inline documentation
- ✅ PHPDoc blocks for all functions
- ✅ Clear variable and function naming
- ✅ Detailed comments explaining complex logic
- ✅ User documentation in readme.txt
- ✅ Developer documentation included

#### **Testing & Validation**
- ✅ WordPress 6.8 compatibility tested
- ✅ PHP 7.4 - 8.2 compatibility verified
- ✅ Multisite environment tested
- ✅ Popular theme compatibility checked
- ✅ Plugin conflict testing performed
- ✅ Performance impact analysis completed

### File Structure
```
wp-security-performance-booster/
├── assets/
│   └── README.md                 # Screenshots documentation
├── languages/
│   ├── wp-security-performance-booster.pot    # Translation template
│   └── wp-security-performance-booster-vi.po  # Vietnamese translation
├── CHANGELOG.md                  # This file
├── README.md                     # Developer documentation
├── readme.txt                    # WordPress.org readme
├── uninstall.php                 # Clean uninstall procedures
├── wp-security-performance-booster.php        # Main plugin file
└── WORDPRESS_ORG_SUBMISSION_GUIDE.md         # Submission guide
```

### For Expert Users

This plugin is specifically designed for expert WordPress users who:
- Understand the security implications of disabling updates
- Have proper security monitoring in place
- Manually manage WordPress updates
- Need complete control over their WordPress environment

### Warning

⚠️ **Important Security Notice**: This plugin disables WordPress automatic updates. Users must manually monitor and apply security updates. Not recommended for production sites without proper security monitoring and update management procedures.

### Support & Contact

- **Developer**: HỒ QUANG HIỂN
- **Company**: DPS.MEDIA  
- **Email**: hello@dps.media
- **Website**: https://dps.media
- **Support**: https://dps.media/support
- **Repository**: https://gitlab.com/hienho.ceo-dpsmedia/wordpress-security-and-performance-booster

### License

This plugin is licensed under the GPL v2 or later.
Copyright 2024 HỒ QUANG HIỂN (DPS.MEDIA)

---

## Future Roadmap

### [1.1.0] - Planned Features
- WordPress 6.9 compatibility
- Additional language support (Spanish, Italian)
- Enhanced security features
- Advanced logging system
- Performance analytics dashboard

### [1.2.0] - Future Enhancements
- White-label customization options
- Advanced filtering rules
- API endpoint for external management
- Integration with popular security plugins

---

*This changelog follows the [Keep a Changelog](https://keepachangelog.com/) format to ensure clear communication of all changes and improvements.*
