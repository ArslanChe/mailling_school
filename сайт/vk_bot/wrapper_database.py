import psycopg2

class DataBase:
	def __init__(self, messenger):
		self.messenger = messenger
		self.connect = psycopg2.connect(database = 'messages', user = 'messages', password = 'MES_654GW', host = '127.0.0.1', port = '5432')
		self.cursor = self.connect.cursor()

	def get_message_id_from_new_messages(self):
		self.cursor.execute(f'SELECT message_id FROM new_messages WHERE {self.messenger}_status = 0 LIMIT 1')
		#*обработка ошибок, если fatchall вернет пустой список(СДЕЛАНО)
		try:
			message_id = self.cursor.fetchall()[0][0]
			return message_id
		except:
			return None

	def update_messenger_status_in_new_messages(self, message_id, status):
		self.cursor.execute(f'UPDATE new_messages SET {self.messenger}_status = {status} WHERE message_id = {message_id}')
		self.connect.commit()
		
	def get_text_and_group_id_from_message_information(self, message_id):
		self.cursor.execute(f'SELECT message, group_id FROM message_information WHERE message_id = {message_id}')
		#*обработка ошибок, если fatchall вернет пустой список(СДЕЛАНО)
		try: 
			return self.cursor.fetchall()[0]
		except:
			return None, None

	def get_messenger_user_id_in_group(self, group_id):
		self.cursor.execute(f'SELECT user_id FROM groups WHERE group_id = {group_id}')
		user_ids = [line[0] for line in self.cursor.fetchall()]
		user_messenger = []
		for user_id in user_ids:
			self.cursor.execute(f'SELECT id_{self.messenger} FROM user_information WHERE user_id = {user_id} and id_{self.messenger} != 0')
			messenger_id = self.cursor.fetchone()
			if (messenger_id is not None):
				user_messenger.append(messenger_id[0])
		return user_messenger
	def put_id_in_user_information(self, code, id):
		try:
			self.cursor.execute(f'SELECT * FROM user_information WHERE code = {code}')
			if self.cursor.fetchone():
				self.cursor.execute(f'UPDATE user_information SET id_{self.messenger} = {id} WHERE code = {code}')
				self.cursor.execute(f'UPDATE user_information SET code = -1 WHERE code = {code}')	
				self.connect.commit()
				return True
		except:
			return False
	def debag_update(self): 
		self.cursor.execute(f'UPDATE new_messages SET {self.messenger}_status = 0 WHERE {self.messenger}_status != 0')
		self.connect.commit()
	def agree_send(self, id):
		self.cursor.execute(f'SELECT user_id FROM user_information WHERE id_{self.messenger} = {id}')
		id_us = self.cursor.fetchone()
		if(id_us == None):
			return False
		self.cursor.execute(f'UPDATE users_messages SET isread = true WHERE user_id = {id_us[0]}')
		self.connect.commit()
		return True