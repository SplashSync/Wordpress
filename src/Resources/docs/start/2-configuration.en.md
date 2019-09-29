---
lang: en
permalink: start/configure
title: Configure the Plugin
---

### Enable the Module 
Configuration of your module is available in plugins interface Plugins >> Splash Connector 

![]({{ "/assets/img/screenshot_1.png" | relative_url }})


### Connect to your Splash Account

First, you need to create access keys for you module in our website. To do so, on Splash workspace, go to **Servers** >> **Add a Server** and note your id & encryption keys. 

![]({{ "/assets/img/screenshot_2.png" | relative_url }})

Then, enter the keys on Plugin's configuration (take care not to forget any character). 

![]({{ "/assets/img/screenshot_3.png" | relative_url }})

### Setup default Parameters

To work correctly, this module need few parameters to be selected. 

##### Default User
Select which user will be used for all actions executed by Splash Module. 
We highly recommend creation of a dedicated user for Splash. 
Be aware Splash Module will take care of Users rights policy, this user must have appropriated right on Prestashop.

### Check results of Self-Tests

Each time you update your configuration, module will verify your parameters and ensure communication with Splash is working fine. 
Ensure all tests are passed... this is critical!

![]({{ "/assets/img/screenshot_4.png" | relative_url }})
