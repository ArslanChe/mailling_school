import telebot
from time import sleep
from threading import Thread
from wrapper_data_base import *

db = DataBase(messenger = 'telegram')
bot = telebot.TeleBot('1767406735:AAGFfVnH9hWmtAB2OcHzxlSopm_66QT9PBw')

keyboard = telebot.types.ReplyKeyboardMarkup(True, True)
keyboard.row('Прочитать')


@bot.message_handler(content_types = ['text'])
def listen(message):
    messenger_id, text = message.from_user.id, message.text
    if text.isdigit():
        if db.put_id_in_user_information(text, messenger_id):
            bot.send_message(chat_id = messenger_id, text =  'Ваш аккаунт привязан.')
        else:
            bot.send_message(chat_id = messenger_id, text = 'Такого кода нет.')
    elif text == 'Прочитать':
        if db.read_messages(messenger_id):
            bot.send_message(chat_id = messenger_id, text = 'Сообщения прочитаны.')
        else:
            bot.send_message(chat_id = messenger_id, text = 'Сообщения не могут быть прочитаны.')

def one_send():
    ms_id = db.get_message_id_from_new_messages()
    print(ms_id)
    if (ms_id is None):
        print("No Mes_Id")
        return None
    db.update_messenger_status_in_new_messages(ms_id, 1)
    text, group_id = db.get_text_and_group_id_from_message_information(ms_id)
    if(text == None):
        print("No Text")
        return None
    print('text', text)
    print('group_id', group_id)
    print()
    users = db.get_messenger_user_id_in_group(group_id)
    for user in users:
        try:
            bot.send_message(chat_id = user, text = text, reply_markup = keyboard)
            print('succesfully', user)
        except Exception as e:
            print('error', user)
            print(e)
        print()
    db.update_messenger_status_in_new_messages(ms_id, 2)

def send():
    while True:
        one_send()
        sleep(30 * 1)


listener = Thread(target = bot.polling)
sender = Thread(target = send)

listener.start()
sender.start()
