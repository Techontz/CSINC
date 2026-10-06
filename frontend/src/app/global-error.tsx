"use client";

export default function GlobalError({ reset }: { error: Error & { digest?: string }; reset: () => void }) {
  return (
    <html lang="en">
      <body style={{ margin: 0, fontFamily: "Georgia, serif", background: "#00294c", color: "#fff" }}>
        <main style={{ minHeight: "100vh", display: "flex", flexDirection: "column", justifyContent: "center", padding: "2rem 8vw" }}>
          <p style={{ letterSpacing: "0.18em", textTransform: "uppercase", fontSize: 12, color: "#e7cf9f", fontFamily: "system-ui" }}>CSinc91</p>
          <h1 style={{ fontSize: "clamp(2rem, 5vw, 4rem)", fontWeight: 400, margin: "1.5rem 0" }}>We’ll be right back.</h1>
          <p style={{ fontFamily: "system-ui", opacity: 0.75, maxWidth: 520 }}>The website is temporarily unavailable. Please try again shortly.</p>
          <button
            type="button"
            onClick={reset}
            style={{ marginTop: 32, alignSelf: "flex-start", background: "#b9852c", color: "#001c35", border: 0, padding: "14px 24px", fontWeight: 600, letterSpacing: "0.14em", textTransform: "uppercase", cursor: "pointer" }}
          >
            Try again
          </button>
        </main>
      </body>
    </html>
  );
}
