import { EditorContent, useEditor, useEditorState } from '@tiptap/react';
import type { Editor } from '@tiptap/react';
import Image from '@tiptap/extension-image';
import { CharacterCount, Placeholder } from '@tiptap/extensions';
import StarterKit from '@tiptap/starter-kit';
import {
    Bold,
    Heading2,
    Heading3,
    ImagePlus,
    Italic,
    Link2,
    List,
    ListOrdered,
    LoaderCircle,
    Minus,
    Pilcrow,
    Quote,
    Redo2,
    Strikethrough,
    Underline,
    Undo2,
} from 'lucide-react';
import { useRef, useState } from 'react';
import type { FormEvent, ReactNode } from 'react';
import { toast } from 'sonner';
import { Button } from '@/components/ui/button';
import { Dialog, DialogContent, DialogDescription, DialogFooter, DialogHeader, DialogTitle } from '@/components/ui/dialog';
import { Input } from '@/components/ui/input';
import { Label } from '@/components/ui/label';
import { Tooltip, TooltipContent, TooltipTrigger } from '@/components/ui/tooltip';
import { route } from '@/lib/routes';
import { cn } from '@/lib/utils';

/** Vitesse de lecture retenue pour le temps de lecture, comme Article::MOTS_PAR_MINUTE. */
export const MOTS_PAR_MINUTE = 200;

type Props = {
    valeur: string;
    onChange: (html: string) => void;
    invalide?: boolean;
    /** Appelé à chaque changement du nombre de mots. */
    onMots?: (mots: number) => void;
};

/**
 * Éditeur riche des articles (Tiptap). Le contenu a la typographie de la page publique
 * (.contenu-article) ; le HTML produit est de toute façon nettoyé par le serveur.
 */
export function EditeurArticle({ valeur, onChange, invalide = false, onMots }: Props) {
    const [lien, setLien] = useState<string | null>(null);
    const [televersement, setTeleversement] = useState(false);
    const fichier = useRef<HTMLInputElement>(null);
    // Les gestionnaires de l'éditeur sont créés une fois : ils lisent l'éditeur par cette référence.
    const refEditeur = useRef<Editor | null>(null);

    const editeur = useEditor({
        extensions: [
            StarterKit.configure({
                heading: { levels: [2, 3] },
                link: { openOnClick: false, autolink: true, defaultProtocol: 'https', HTMLAttributes: { target: null, rel: null } },
            }),
            Image,
            Placeholder.configure({ placeholder: 'Écris ton article… Les intertitres (H2) forment le sommaire.' }),
            CharacterCount,
        ],
        content: valeur,
        editorProps: {
            attributes: {
                class: 'contenu-article min-h-[480px] px-5 py-6 focus:outline-none md:px-10 md:py-8',
                'aria-label': "Contenu de l'article",
                role: 'textbox',
                'aria-multiline': 'true',
            },
            // Images glissées ou collées : téléversées puis insérées.
            handleDrop: (_vue, evenement) => insererImages(evenement.dataTransfer?.files),
            handlePaste: (_vue, evenement) => insererImages(evenement.clipboardData?.files),
        },
        onUpdate: ({ editor }) => {
            onChange(editor.isEmpty ? '' : editor.getHTML());
            onMots?.(editor.storage.characterCount.words());
        },
        onCreate: ({ editor }) => onMots?.(editor.storage.characterCount.words()),
    });

    refEditeur.current = editeur;

    const etat = useEditorState({
        editor: editeur,
        selector: ({ editor }) => ({
            paragraphe: editor?.isActive('paragraph') ?? false,
            h2: editor?.isActive('heading', { level: 2 }) ?? false,
            h3: editor?.isActive('heading', { level: 3 }) ?? false,
            gras: editor?.isActive('bold') ?? false,
            italique: editor?.isActive('italic') ?? false,
            souligne: editor?.isActive('underline') ?? false,
            barre: editor?.isActive('strike') ?? false,
            puces: editor?.isActive('bulletList') ?? false,
            numeros: editor?.isActive('orderedList') ?? false,
            citation: editor?.isActive('blockquote') ?? false,
            lien: editor?.isActive('link') ?? false,
            annuler: editor?.can().undo() ?? false,
            retablir: editor?.can().redo() ?? false,
        }),
    });

    function insererImages(fichiers: FileList | null | undefined): boolean {
        const images = Array.from(fichiers ?? []).filter((f) => f.type.startsWith('image/'));

        const actuel = refEditeur.current;

        if (!images.length || !actuel) {
            return false;
        }

        images.forEach((image) => void televerserImage(actuel, image, setTeleversement));

        return true;
    }

    const ouvrirLien = () => setLien(editeur?.getAttributes('link').href ?? '');

    const appliquerLien = (evenement: FormEvent) => {
        evenement.preventDefault();

        if (!editeur || lien === null) {
            return;
        }

        const href = lien.trim();
        const chaine = editeur.chain().focus().extendMarkRange('link');

        if (!href) {
            chaine.unsetLink().run();
        } else if (editeur.state.selection.empty && !editeur.isActive('link')) {
            chaine.insertContent({ type: 'text', text: href, marks: [{ type: 'link', attrs: { href } }] }).run();
        } else {
            chaine.setLink({ href }).run();
        }

        setLien(null);
    };

    if (!editeur) {
        return <div className="min-h-[540px] animate-pulse rounded-xl bg-muted" />;
    }

    return (
        <div
            className={cn(
                'relative rounded-xl border bg-card shadow-xs transition-[color,box-shadow] focus-within:border-ring focus-within:ring-[3px] focus-within:ring-ring/30',
                invalide && 'border-destructive',
            )}
        >
            <div
                role="toolbar"
                aria-label="Mise en forme"
                className="sticky top-0 z-10 flex flex-wrap items-center gap-0.5 rounded-t-xl border-b bg-card/95 p-1.5 backdrop-blur"
            >
                <Outil libelle="Paragraphe" actif={etat.paragraphe} onClick={() => editeur.chain().focus().setParagraph().run()}>
                    <Pilcrow />
                </Outil>
                <Outil libelle="Intertitre (H2)" actif={etat.h2} onClick={() => editeur.chain().focus().toggleHeading({ level: 2 }).run()}>
                    <Heading2 />
                </Outil>
                <Outil libelle="Sous-titre (H3)" actif={etat.h3} onClick={() => editeur.chain().focus().toggleHeading({ level: 3 }).run()}>
                    <Heading3 />
                </Outil>
                <Separateur />
                <Outil libelle="Gras" raccourci="⌘B" actif={etat.gras} onClick={() => editeur.chain().focus().toggleBold().run()}>
                    <Bold />
                </Outil>
                <Outil libelle="Italique" raccourci="⌘I" actif={etat.italique} onClick={() => editeur.chain().focus().toggleItalic().run()}>
                    <Italic />
                </Outil>
                <Outil libelle="Souligné" raccourci="⌘U" actif={etat.souligne} onClick={() => editeur.chain().focus().toggleUnderline().run()}>
                    <Underline />
                </Outil>
                <Outil libelle="Barré" actif={etat.barre} onClick={() => editeur.chain().focus().toggleStrike().run()}>
                    <Strikethrough />
                </Outil>
                <Separateur />
                <Outil libelle="Liste à puces" actif={etat.puces} onClick={() => editeur.chain().focus().toggleBulletList().run()}>
                    <List />
                </Outil>
                <Outil libelle="Liste numérotée" actif={etat.numeros} onClick={() => editeur.chain().focus().toggleOrderedList().run()}>
                    <ListOrdered />
                </Outil>
                <Outil libelle="Citation" actif={etat.citation} onClick={() => editeur.chain().focus().toggleBlockquote().run()}>
                    <Quote />
                </Outil>
                <Outil libelle="Séparateur" onClick={() => editeur.chain().focus().setHorizontalRule().run()}>
                    <Minus />
                </Outil>
                <Separateur />
                <Outil libelle="Lien" raccourci="⌘K" actif={etat.lien} onClick={ouvrirLien}>
                    <Link2 />
                </Outil>
                <Outil libelle="Image" disabled={televersement} onClick={() => fichier.current?.click()}>
                    {televersement ? <LoaderCircle className="animate-spin" /> : <ImagePlus />}
                </Outil>
                <div className="ml-auto flex items-center gap-0.5">
                    <Outil libelle="Annuler" raccourci="⌘Z" disabled={!etat.annuler} onClick={() => editeur.chain().focus().undo().run()}>
                        <Undo2 />
                    </Outil>
                    <Outil libelle="Rétablir" raccourci="⇧⌘Z" disabled={!etat.retablir} onClick={() => editeur.chain().focus().redo().run()}>
                        <Redo2 />
                    </Outil>
                </div>
                <input
                    ref={fichier}
                    type="file"
                    accept="image/jpeg,image/png,image/webp,image/gif"
                    className="hidden"
                    onChange={(evenement) => {
                        insererImages(evenement.target.files);
                        evenement.target.value = '';
                    }}
                />
            </div>

            <EditorContent
                editor={editeur}
                onKeyDown={(evenement) => {
                    if ((evenement.metaKey || evenement.ctrlKey) && evenement.key.toLowerCase() === 'k') {
                        evenement.preventDefault();
                        ouvrirLien();
                    }
                }}
            />

            <Dialog open={lien !== null} onOpenChange={(ouvert) => !ouvert && setLien(null)}>
                <DialogContent className="sm:max-w-md">
                    <form onSubmit={appliquerLien} className="flex flex-col gap-4">
                        <DialogHeader>
                            <DialogTitle>{etat.lien ? 'Modifier le lien' : 'Ajouter un lien'}</DialogTitle>
                            <DialogDescription>Adresse d'une page du site (/tarifs) ou d'un autre site (https://…). Laisse vide pour retirer le lien.</DialogDescription>
                        </DialogHeader>
                        <div className="flex flex-col gap-1.5">
                            <Label htmlFor="lien">Adresse</Label>
                            <Input id="lien" autoFocus value={lien ?? ''} onChange={(evenement) => setLien(evenement.target.value)} placeholder="https://…" />
                        </div>
                        <DialogFooter>
                            {etat.lien && (
                                <Button type="button" variant="ghost" className="mr-auto text-destructive" onClick={() => setLien('')}>
                                    Retirer le lien
                                </Button>
                            )}
                            <Button type="button" variant="outline" onClick={() => setLien(null)}>
                                Annuler
                            </Button>
                            <Button type="submit">Appliquer</Button>
                        </DialogFooter>
                    </form>
                </DialogContent>
            </Dialog>
        </div>
    );
}

/** Téléverse une image du contenu et l'insère à la position du curseur. */
async function televerserImage(editeur: Editor, image: File, setTeleversement: (enCours: boolean) => void): Promise<void> {
    if (image.size > 4 * 1024 * 1024) {
        toast.error(`« ${image.name} » dépasse 4 Mo.`);

        return;
    }

    const donnees = new FormData();
    donnees.append('image', image);
    setTeleversement(true);

    try {
        const reponse = await fetch(route('admin.articles.image'), {
            method: 'POST',
            body: donnees,
            headers: { Accept: 'application/json', 'X-XSRF-TOKEN': jetonCsrf() },
            credentials: 'same-origin',
        });

        if (!reponse.ok) {
            throw new Error((await reponse.json().catch(() => null))?.message ?? 'Téléversement impossible.');
        }

        const { url } = (await reponse.json()) as { url: string };
        const alt = image.name.replace(/\.[^.]+$/, '').replace(/[-_]+/g, ' ');
        editeur.chain().focus().setImage({ src: url, alt }).run();
    } catch (erreur) {
        toast.error(erreur instanceof Error ? erreur.message : 'Téléversement impossible.');
    } finally {
        setTeleversement(false);
    }
}

/** Jeton CSRF du cookie XSRF-TOKEN posé par Laravel. */
function jetonCsrf(): string {
    const cookie = document.cookie.split('; ').find((ligne) => ligne.startsWith('XSRF-TOKEN='));

    return cookie ? decodeURIComponent(cookie.split('=')[1]) : '';
}

function Outil({
    libelle,
    raccourci,
    actif = false,
    disabled = false,
    onClick,
    children,
}: {
    libelle: string;
    raccourci?: string;
    actif?: boolean;
    disabled?: boolean;
    onClick: () => void;
    children: ReactNode;
}) {
    return (
        <Tooltip>
            <TooltipTrigger asChild>
                <Button
                    type="button"
                    variant="ghost"
                    size="icon"
                    className={cn('size-8 text-muted-foreground', actif && 'bg-menthe text-foret hover:bg-menthe')}
                    aria-label={libelle}
                    aria-pressed={actif}
                    disabled={disabled}
                    onMouseDown={(evenement) => evenement.preventDefault()}
                    onClick={onClick}
                >
                    {children}
                </Button>
            </TooltipTrigger>
            <TooltipContent>
                {libelle}
                {raccourci && <span className="ml-2 opacity-60">{raccourci}</span>}
            </TooltipContent>
        </Tooltip>
    );
}

function Separateur() {
    return <div className="mx-1 h-5 w-px bg-border" aria-hidden="true" />;
}
