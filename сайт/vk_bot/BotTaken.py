import vk_api
from vk_api.keyboard import VkKeyboard, VkKeyboardColor
from vk_api.longpoll import VkLongPoll, VkEventType
import psycopg2
from wrapper_database import*

# Пояснения:
# wrapper_database - работа с базой данных

def create_keyboard():
    keyboard = vk_api.keyboard.VkKeyboard(one_time=False)

    keyboard.add_button("Закрыть", color=vk_api.keyboard.VkKeyboardColor.DEFAULT)
    keyboard.add_button("Кнопка", color=vk_api.keyboard.VkKeyboardColor.POSITIVE)

    keyboard.add_line()
    keyboard.add_button("Кнопка", color=vk_api.keyboard.VkKeyboardColor.NEGATIVE)

    keyboard.add_line()
    keyboard.add_button("Кнопка", color=vk_api.keyboard.VkKeyboardColor.PRIMARY)
    keyboard.add_button("Кнопка", color=vk_api.keyboard.VkKeyboardColor.PRIMARY)

    return keyboard.get_keyboard()


# Проверка что текст - int:
def text_int(text):
	set_int = {'0', '1', '2' , '3', '4', '5', '6', '7', '8', '9'}
	if(isinstance(text, str)):
		if (len(text) >= 16):
			return False
		for i in text:
			if (i not in set_int):
				return False
		return True
	elif(isinstance(text, int)):
		return True
	else:
		return False

# Отправка сообщения через бота:
def sender(id, text):
	vk.messages.send(user_id = id, message = text, random_id = 0, keyboard = keyboard.get_keyboard())

# Отправка стикера через бота:
def send_stick(id, number):
	vk.messages.send(user_id = id, sticker_id = number, random_id = 0)

# Отправка фото через бота:
def send_photo(id, url):
	vk.messages.send(user_id = id, attachment = url, random_id = 0)

# Сессия вк
vk_session = vk_api.VkApi(token = "d89efc1bd4353b275d8f9589c423b511d9eab335ddce1842bee1707ffebc1c85b651bda07f144f260dc2f")
vk = vk_session.get_api()
# Работа с базой данных
db = DataBase(messenger = 'vk')

#ожидание действия со стороны пользователя
longpoll = VkLongPoll(vk_session)

#просмотр принятых сообщений

keyboard = VkKeyboard(one_time=False)
keyboard.add_button(label="Прочитать", color=VkKeyboardColor.POSITIVE)
for event in longpoll.listen():
	if event.type == VkEventType.MESSAGE_NEW:
		if event.to_me:
			id = event.user_id
			msg = event.text
			if (text_int(msg)):
				if(db.put_id_in_user_information(msg, id)):
					sender(id, "зарегистрирован")
				else:
						sender(id, "проверьте код подтверждения или обратитесь к преподавателям")
			else:
				if(msg == "Прочитать"):
					if (db.agree_send(id) == True):
						sender(id, "Прочтение подтверждено")
					else:
						sender(id, "Пользователь не найден, зарегистрируйтесь или обратитесь к преподавателям")
				else:
					sender(id, "Для подтверждения прочтения нажмите на кнопку Прочитать")
