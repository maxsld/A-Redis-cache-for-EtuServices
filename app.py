import redis
import time
from flask import Flask, request, jsonify

app = Flask(__name__)

# Connexion à Redis (assurez-vous que Redis fonctionne sur localhost)
client = redis.StrictRedis(host='localhost', port=6379, db=0)

def verifier_connexion_utilisateur(email):
    # La clé Redis associée à l'utilisateur
    user_key = f"user:{email}:connexions"
    
    # Clé de verrouillage pour éviter les doublons
    lock_key = f"user:{email}:lock"
    
    # Vérifier si un verrou existe (s'il existe, cela signifie qu'une connexion est en cours)
    if client.get(lock_key):
        # Si le verrou est présent, cela signifie que la connexion est en cours
        return False
    
    # Mettre un verrou pour empêcher d'autres connexions simultanées
    client.setex(lock_key, 5, "locked")  # Le verrou est valable pendant 5 secondes
    
    # Ajouter un timestamp actuel dans une liste Redis pour gérer la fenêtre de 10 minutes
    current_time = int(time.time())
    client.lpush(user_key, current_time)

    # Enlever les connexions qui datent de plus de 10 minutes (600 secondes)
    client.ltrim(user_key, 0, 9)

    # Récupérer toutes les connexions
    connexions = client.lrange(user_key, 0, -1)
    
    # Nombre de connexions dans les 10 dernières minutes
    nombre_connexions = len(connexions)
    print(f"Nombre de connexions dans les 10 dernières minutes : {nombre_connexions}")
    
    # Si le nombre de connexions dépasse 10 dans les 10 dernières minutes
    if nombre_connexions >= 10:
        # Si l'utilisateur a plus de 10 connexions dans les 10 dernières minutes, bloquer l'accès
        # Libérer le verrou
        client.delete(lock_key)
        return False

    # Libérer le verrou après avoir ajouté la connexion
    client.delete(lock_key)

    # Sinon, l'utilisateur est autorisé à se connecter
    return True

@app.route('/verifier-connexion', methods=['GET'])
def verifier_connexion():
    email = request.args.get('email')
    
    if not email:
        return jsonify({"error": "Email manquant"}), 400
    
    # Vérification des connexions de l'utilisateur
    if verifier_connexion_utilisateur(email):
        return jsonify({"message": "Connexion autorisée"})
    else:
        # Si plus de 10 connexions dans les 10 dernières minutes
        return jsonify({"message": "Limite de connexions atteinte. Veuillez réessayer plus tard."}), 403

if __name__ == '__main__':
    app.run(debug=True, host='0.0.0.0', port=5000)
