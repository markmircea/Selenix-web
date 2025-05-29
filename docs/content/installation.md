# Installation Guide

Installing Selenix is quick and easy. Follow the instructions for your operating system below.

## System Requirements

Before installing Selenix, make sure your system meets these requirements:

- **Operating System**: Windows 10+, macOS 10.14+, or Linux (Ubuntu 18.04+)
- **Browser**: Chrome 88+ or Edge 88+ (required for automation)
- **RAM**: Minimum 4GB, recommended 8GB
- **Storage**: 500MB free space

## Windows Installation

### Method 1: Download Installer (Recommended)

1. Visit our [download page](https://selenix.io/download)
2. Click **Download for Windows**
3. Run the downloaded `.exe` file
4. Follow the installation wizard
5. Launch Selenix from your Start menu

### Method 2: Microsoft Store

1. Open the **Microsoft Store**
2. Search for "Selenix"
3. Click **Get** to install
4. Launch from Start menu or desktop

## macOS Installation

### Method 1: Download DMG

1. Visit our [download page](https://selenix.io/download)
2. Click **Download for macOS**
3. Open the downloaded `.dmg` file
4. Drag Selenix to your Applications folder
5. Launch from Applications or Spotlight

### Method 2: Homebrew

```bash
brew install --cask selenix
```

## Linux Installation

### Ubuntu/Debian (.deb package)

```bash
# Download the .deb package
wget https://releases.selenix.io/selenix_latest_amd64.deb

# Install using apt
sudo apt install ./selenix_latest_amd64.deb
```

### Fedora/CentOS (.rpm package)

```bash
# Download the .rpm package  
wget https://releases.selenix.io/selenix_latest.x86_64.rpm

# Install using dnf
sudo dnf install ./selenix_latest.x86_64.rpm
```

### AppImage (Universal)

```bash
# Download AppImage
wget https://releases.selenix.io/Selenix_latest.AppImage

# Make executable
chmod +x Selenix_latest.AppImage

# Run
./Selenix_latest.AppImage
```

## Browser Extension

After installing the desktop application, you'll need to install the browser extension to enable recording and automation.

### Chrome Extension

1. Open the [Chrome Web Store](https://chrome.google.com/webstore/detail/selenix/abc123)
2. Click **Add to Chrome**
3. Click **Add extension** in the popup
4. Pin the extension to your toolbar for easy access

### Edge Extension

1. Open the [Microsoft Edge Add-ons](https://microsoftedge.microsoft.com/addons/detail/selenix/abc123)
2. Click **Get**
3. Click **Add extension** in the popup
4. Pin the extension to your toolbar

## Verification

To verify everything is working correctly:

1. **Launch the Selenix desktop app**
2. **Open your browser** and click the Selenix extension icon
3. **Check connection status** - you should see "Connected to Selenix Desktop"
4. **Try recording** a simple action to test functionality

If you see any connection issues, try restarting both the desktop app and your browser.

## Troubleshooting

### Common Issues

**Extension not connecting to desktop app:**
- Make sure the desktop app is running
- Check that both app and extension are the latest versions
- Restart your browser and try again

**Installation blocked by antivirus:**
- Add Selenix to your antivirus whitelist
- Temporarily disable real-time protection during installation

**Permission denied on Linux:**
- Make sure you have write permissions to the installation directory
- Try running the installer with `sudo` if needed

Need more help? Check our [support page](https://selenix.io/support) or contact us at support@selenix.io.
