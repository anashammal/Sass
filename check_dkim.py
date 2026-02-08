import base64
key = "MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEA1q6jTG8t50yxXAFqHC87C8F9P1CTGDNIAksfLngvA6mcjOnTVP2NU2qCccJ7nfFHm7smcC8foxA0vnbX0DT/T8ysog0OxeG8AagcAfTDf3sw4fFQKHIWY4B5nv65zpR1VnAJuxggDS7/TN54RtztdeauxSPKdrdseV221bs7UR8Fb385HKRfFeVvOcSt8WryCJXNux9UDcfXMZBGPTWv4/H5cGpqx27KA6xSpy/t1saOsLW8TC1DA3AB"
try:
    decoded = base64.b64decode(key)
    print(f"Decoded length: {len(decoded)}")
    print("Base64 is valid.")
except Exception as e:
    print(f"Error: {e}")
