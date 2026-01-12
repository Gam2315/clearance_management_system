from smartcard.System import readers
from smartcard.util import toHexString
import requests
import time

def send_to_laravel(uid):
    try:
        formatted_uid = uid.upper()
        headers = {
            'Content-Type': 'application/x-www-form-urlencoded',
            'Accept': 'application/json'
        }
        response = requests.post("http://127.0.0.1:8000/api/nfc-tap",
                               data={"uid": formatted_uid},
                               headers=headers)

        print("Sent UID:", formatted_uid)
        print("Status Code:", response.status_code)

        try:
            json_response = response.json()
            if json_response.get('success'):
                if json_response.get('already_linked'):
                    print("🔗 LINKED:", json_response.get('message'))
                    if 'student_info' in json_response:
                        student_info = json_response['student_info']
                        print(f"   Already linked to: {student_info['name']} ({student_info['student_number']})")
                else:
                    print("✅ SUCCESS:", json_response.get('message'))
                    print("   UID stored successfully - ready for linking")
            else:
                print("❌ ERROR:", json_response.get('message'))
                if 'student_info' in json_response:
                    student_info = json_response['student_info']
                    print(f"   Already linked to: {student_info['name']} ({student_info['student_number']})")
        except ValueError:
            print("Response is not JSON:", response.text[:200])

    except Exception as e:
        print("Failed to connect to Laravel:", e)
def get_uid():
    r = readers()
    if not r:
        print("No NFC reader found.")
        return

    reader = r[0]
    print("Using reader:", reader)

    last_uid = None
    last_read_time = 0

    while True:
        connection = reader.createConnection()
        try:
            connection.connect()
            GET_UID = [0xFF, 0xCA, 0x00, 0x00, 0x00]
            data, sw1, sw2 = connection.transmit(GET_UID)
            if sw1 == 0x90 and sw2 == 0x00:
                uid = toHexString(data).replace(" ", "")
                current_time = time.time()

                if uid != last_uid or (current_time - last_read_time > 5):
                    send_to_laravel(uid)
                    last_uid = uid
                    last_read_time = current_time
        except:
            pass
        time.sleep(5)
      

get_uid()
