from flask import Flask, request, jsonify
import random

app = Flask(__name__)

@app.route("/generate_quiz", methods=["POST"])
def generate_quiz():

    topic = request.json.get("topic")

    questions = [
        {
            "question": "What is supervised learning?",
            "options": [
                "Learning without labeled data",
                "Learning with labeled data",
                "Random guessing",
                "Database storage"
            ],
            "answer": 1
        },
        {
            "question": "What is overfitting?",
            "options": [
                "Model memorizes training data",
                "Model deletes data",
                "Model compresses features",
                "Model removes labels"
            ],
            "answer": 0
        },
        {
            "question": "What does regression predict?",
            "options": [
                "Categories",
                "Images",
                "Continuous values",
                "Binary output only"
            ],
            "answer": 2
        }
    ]

    random.shuffle(questions)

    return jsonify({
        "questions": questions
    })


app.run(port=5000)