Introduction
======

[![MIT licensed](https://img.shields.io/badge/license-MIT-blue.svg)](./LICENSE) 
[![PHP Composer](https://github.com/famoser/nodika/actions/workflows/php.yml/badge.svg)](https://github.com/famoser/nodika/actions/workflows/php.yml)
[![Node.js Encore](https://github.com/famoser/nodika/actions/workflows/node.js.yml/badge.svg)](https://github.com/famoser/nodika/actions/workflows/node.js.yml)

Used by the veterinarians of Basel-Landschaft, Switzerland, this tool organizes emergency services.

Core features:
- Distribute emergency services evenly and fairly to clinics. The purpose-specific algorithm treats weekdays, saturdays, sundays and holydays differently, and can handle clinics of different sizes and responsibilities. A random but fair allocation results.   
- If some dates do not fit, clinics may trade emergency services with each other.
- Doctors part of the reponsible clinic are reminded about upcoming emergency services.

*Notice about the state of the project: This project would be in need of a bigger maintenance-related refactoring, in particular to lift vue v2 to vue 3, but also the backend-dependencies. This will be undertaken in 2026. Until then, only critical vulnerabilities in the backend will be addressed (which is sufficient, as all users are semi-trusted).*
