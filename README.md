# WordPress Security & Performance Booster

[![Version](https://img.shields.io/badge/version-1.0.0-blue.svg)](https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster/releases)
[![WordPress](https://img.shields.io/badge/WordPress-4.0+-blue.svg)](https://wordpress.org)
[![PHP](https://img.shields.io/badge/PHP-7.4+-purple.svg)](https://php.net)
[![License](https://img.shields.io/badge/license-GPL%20v2-green.svg)](LICENSE)

A comprehensive WordPress plugin that enhances security and performance by disabling updates, preventing spam, and cleaning the admin interface.

## Table of Contents

- [Features](#features)
- [Installation](#installation)
- [Screenshots](#screenshots)
- [Configuration](#configuration)
- [Language Support](#language-support)
- [Requirements](#requirements)
- [Security Notice](#security-notice)
- [Support](#support)
- [Contributing](#contributing)
- [Changelog](#changelog)
- [License](#license)

## Features

### Update Control
- **Disable WordPress Updates**: Blocks all WordPress core, plugin, and theme update checks
- **Reduce Server Load**: Eliminates background update processes and cronjobs
- **Prevent Automatic Updates**: Stops all forms of automatic updates

### Comment Protection
- **Disable Comments**: Completely removes comments across all post types
- **Block Pingbacks & Trackbacks**: Prevents pingback and trackback spam
- **Remove Comment UI**: Cleans admin menus and comment-related interfaces

### Security Enhancement
- **Disable XML-RPC**: Blocks XML-RPC functionality to prevent brute force attacks
- **Attack Prevention**: Comprehensive protection against common WordPress vulnerabilities
- **Secure Headers**: Removes potentially dangerous HTTP headers

### Interface Cleanup
- **Hide Admin Notifications**: Removes plugin/theme promotional notifications
- **Clean Dashboard**: Removes unnecessary dashboard widgets
- **Streamlined UI**: Creates a cleaner, distraction-free admin experience

## Installation

### Method 1: Manual Installation
1. Download the latest release from [GitHub Releases](https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster/releases)
2. Extract the zip file
3. Upload the `wp-security-performance-booster` folder to `/wp-content/plugins/`
4. Activate the plugin through the WordPress admin panel
5. Go to **Settings → Security Booster** to configure

### Method 2: Git Clone
```bash
cd /path/to/wordpress/wp-content/plugins/
git clone https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster.git wp-security-performance-booster
```

## Screenshots

### Modern Admin Interface
![Admin Interface](assets/screenshot-1.png)
*Modern, flat-designed settings page with DPS.MEDIA branding*

### Feature Control
![Feature Controls](assets/screenshot-2.png)
*Selective feature control with toggle switches and descriptions*

### Language Support
![Language Switcher](assets/screenshot-3.png)
*Multi-language support with easy language switching*

## Configuration

Navigate to **WordPress Admin → Settings → Security Booster** to access the plugin settings.

### Available Options:

#### Update Control
- **Disable WordPress Updates**: ✅ Recommended for staging/development
- **Benefits**: Reduces server load, prevents unexpected changes

#### Comment Protection  
- **Disable Comments**: ✅ Recommended for business sites
- **Block Pingbacks & Trackbacks**: ✅ Highly recommended
- **Benefits**: Eliminates spam, improves security

#### Security Enhancement
- **Disable XML-RPC**: ✅ Highly recommended
- **Benefits**: Prevents brute force attacks, improves security

#### Interface Cleanup
- **Hide Admin Notifications**: ✅ Recommended
- **Clean Dashboard**: ✅ Recommended  
- **Benefits**: Cleaner interface, better focus

## Support

The plugin supports multiple languages with automatic detection:

- 🇺🇸 **English** (en_US) - Default
- 🇻🇳 **Tiếng Việt** (vi) - Complete translation
- 🇩🇪 **Deutsch** (de_DE) - German
- 🇫🇷 **Français** (fr_FR) - French

### Language Features:
- **Automatic Detection**: Uses WordPress locale by default
- **Manual Override**: Language switcher in admin settings
- **Persistent Storage**: Remembers user language preference
- **Professional Translation**: All strings properly localized

## Requirements

- **WordPress**: 4.0 or higher
- **PHP**: 7.4 or higher
- **MySQL**: 5.6 or higher
- **Server**: Apache/Nginx with mod_rewrite

### Compatibility:
- ✅ WordPress Multisite
- ✅ WordPress 6.0+
- ✅ PHP 8.0+
- ✅ Latest browsers

## Security Notice

**Important**: This plugin is designed for expert users who understand the security implications of disabling updates.

### Best Practices:
1. **Manual Updates**: Regularly check for WordPress/plugin updates manually
2. **Security Monitoring**: Implement proper security monitoring
3. **Staging Environment**: Perfect for development/staging sites
4. **Backup Strategy**: Maintain regular backups

### When to Use:
- ✅ Development environments
- ✅ Staging sites
- ✅ Sites with managed hosting
- ✅ Version-controlled deployments

### When NOT to Use:
- ❌ Production sites without security monitoring
- ❌ Sites without manual update management
- ❌ Beginner WordPress users

## Support

### Developer Information
- **Developer**: HỒ QUANG HIỂN
- **Company**: DPS.MEDIA
- **Email**: [hello@dps.media](mailto:hello@dps.media)
- **Website**: [dps.media](https://dps.media)
- **Support**: [dps.media/support](https://dps.media/support)

### Getting Help
1. **Documentation**: Check this README and plugin settings
2. **Issues**: [GitHub Issues](https://github.com/hienhoceo-dpsmedia/WordPress-Security-Performance-Booster/issues)
3. **Email Support**: hello@dps.media
4. **Professional Support**: Available through DPS.MEDIA

## Contributing

We welcome contributions! Please read our contributing guidelines:

### How to Contribute:
1. Fork the repository
2. Create a feature branch: `git checkout -b feature/new-feature`
3. Make your changes
4. Test thoroughly
5. Commit: `git commit -am 'Add new feature'`
6. Push: `git push origin feature/new-feature`
7. Submit a Pull Request

### Areas for Contribution:
- 🌍 **Translations**: Add support for more languages
- 🐛 **Bug Fixes**: Report and fix issues
- ✨ **Features**: Suggest and implement new features
- 📖 **Documentation**: Improve documentation

## Changelog

### Version 1.0.0
**Initial Release**

#### New Features:
- **Modern Admin Interface**: Flat, responsive design with DPS.MEDIA branding
- **Selective Feature Control**: Toggle individual features on/off
- **Multi-language Support**: Vietnamese, German, French translations
- **Language Switcher**: Easy language selection in admin
- **Enhanced Security**: XML-RPC blocking, comprehensive spam protection
- **Performance Optimization**: Reduced server load, optimized code

#### Improvements:
- **Better UX**: Card-based interface with clear descriptions
- **Professional Branding**: Complete DPS.MEDIA integration
- **Code Architecture**: Modular, maintainable codebase
- **Compatibility**: WordPress 6.0+ and PHP 8.0+ support

#### Changes:
- **Plugin Name**: "WordPress Security & Performance Booster"
- **Text Domain**: `wp-security-performance-booster`
- **Developer**: HỒ QUANG HIỂN / DPS.MEDIA
- **All Files Renamed**: Consistent naming throughout

### Previous Versions (1.0-1.8.0)
- Legacy update blocking functionality
- Basic WordPress compatibility
- Simple admin interface

## License

This project is licensed under the GPL v2 License - see the [LICENSE](LICENSE) file for details.

```
Copyright (C) 2024 HỒ QUANG HIỂN

This program is free software; you can redistribute it and/or modify
it under the terms of the GNU General Public License as published by
the Free Software Foundation; either version 2 of the License, or
(at your option) any later version.
```

---

<div align="center">

**Made by [DPS.MEDIA](https://dps.media)**

[![Website](https://img.shields.io/badge/Website-dps.media-blue)](https://dps.media)
[![Email](https://img.shields.io/badge/Email-hello%40dps.media-red)](mailto:hello@dps.media)
[![Support](https://img.shields.io/badge/Support-dps.media%2Fsupport-green)](https://dps.media/support)

</div>
