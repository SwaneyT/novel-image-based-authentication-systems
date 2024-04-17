#!C:\Users\icysh\AppData\Local\Programs\Python\Python38\python.exe

import cv2
import hashlib
import mahotas
import bcrypt
import sys
import numpy as np
from hashlib import sha3_384

# Step 1: Feature Extraction
def extract_features(image_path):
    # Use OpenCV or another library to load and preprocess the image
    image = cv2.imread(image_path)
    gray_image = cv2.cvtColor(image, cv2.COLOR_BGR2GRAY)
    
    # Feature extraction algorithm (e.g., LBP)
    lbp_features = mahotas.features.lbp(gray_image, radius=4, points=6).astype(np.uint16)
    
    # hog = cv2.HOGDescriptor()
    # hog_features = hog.compute(image)

    # Color histogram feature extraction
    color_hist_features = cv2.calcHist([image], [0, 1, 2], None, [8, 8, 8], [0, 256, 0, 256, 0, 256]).astype(np.uint16)

    # Combine all features
    features = {
        'lbp': lbp_features.flatten(),
        # 'hog': hog_features.flatten(),
        'color_hist': color_hist_features.flatten()
    }

    return features

def reduce_with_sha3(long_string):
  hash_obj = sha3_384(long_string.encode())
  return hash_obj.hexdigest()

image_path = str(sys.argv[1])
features = extract_features(image_path)

features_str = ''
for feature_type, feature in features.items():
    for i in range(len(feature)):
        features_str += f'{feature[i].astype(np.uint16)}'

hashed_string = reduce_with_sha3(features_str)

# print(features_str)
# print(len(features_str))
print(hashed_string[:72])