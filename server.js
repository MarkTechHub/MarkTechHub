import express from "express";
import fetch from "node-fetch";
import cors from "cors";

const app = express();
app.use(cors());
app.use(express.json());

const OPENAI_KEY = "WEKA_API_KEY_YAKO_HAPA";

app.post("/ask", async (req, res) => {
  const question = req.body.question;

  try {
    const response = await fetch("https://api.openai.com/v1/chat/completions", {
      method: "POST",
      headers: {
        "Content-Type": "application/json",
        "Authorization": `Bearer ${OPENAI_KEY}`
      },
      body: JSON.stringify({
        model: "gpt-4.1-mini",
        messages: [
          {
            role: "system",
            content: "You are a helpful technology assistant. Answer clearly."
          },
          {
            role: "user",
            content: question
          }
        ]
      })
    });

    const data = await response.json();
    res.json({ answer: data.choices[0].message.content });

  } catch (err) {
    res.status(500).json({ error: "AI error" });
  }
});

app.listen(3000, () => {
  console.log("AI server running on port 3000");
});
