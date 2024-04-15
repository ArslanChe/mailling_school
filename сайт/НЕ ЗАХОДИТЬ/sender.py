import psycopg2

class DataBase:
    def __init__(self):
        self.connect = psycopg2.connect(database = 'messages', user = 'messages', password = 'MES_654GW', host = '127.0.0.1', port = '5432')
        self.cursor = self.connect.cursor() 

    def a(self):
        self.cursor.execute('SELECT (message_id) FROM new_messages WHERE telegram_status = 0 LIMIT 1')
        message_id = self.cursor.fetchall()[0][0]
        self.cursor.execute(f"UPDATE new_messages SET telegram_status = 1 WHERE message_id = {message_id}")
        
        print(message_id)
        
        '''
        self.cursor.execute('SELECT * FROM new_messages')
        print(self.cursor.fetchall())
        '''
        
        self.cursor.execute(f'SELECT group_id, message FROM message_information WHERE message_id = {message_id}')
        
        print(type(self.cursor.fetchall()[0][0]))
        
        return 0
        
        group_id, text = self.cursor.fetchall()[0][0]
        
        print(group_id, '|||', text)
        

db = DataBase()
db.a()
