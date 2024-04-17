# novel-image-based-authentication-systems
Two novel image-based authentication systems were designed to take incorporate images into passwords instead of text to improve the difficulty of passwords against attackers. The authentication system was built with HTML, CSS, JavaScript, PHP and Python. The project was built on the Apache HTTP Server through XAMPP. The authentication system uses BCRYPT with 12 rounds to hash passwords by default.

Image-Based Sequential Password:
The image-based sequential password is a customisable grid of images (default 3x3), where the user can select a sequence of images in order to authenticate to an account. The password is transferred as a string to the database server ("bush-tree-car-watch-cat-dog"). It is recommended that the administrator modifies the grid to be 5x5 (25 images).

Single-Image Password:
The single-image password takes an image input instead of a string input. The image is then dissected server-side for its key features, formed into a long string, hashed for a uniform length, and then transferred to the database server to be hashed again.

The authentication system is experimental; thus testing and caution are advised.
